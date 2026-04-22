@extends('layouts.auth')

@section('title', 'Reset Password')
@section('auth_heading', 'Create or reset your password')
@section('auth_copy', 'Enter your staff email address to receive a secure reset link. Use it to create your password or recover access.')
@section('auth_media_heading', 'Reset access securely')
@section('auth_media_copy', 'Password reset links are the controlled path for first-time password creation and account recovery across the CRM workspace.')

@section('auth_inline_email_errors', '1')

@section('auth_content')
    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="email">Email address <span class="auth-required">*</span></label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Enter your staff email address" required autocomplete="email" autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-link-stack auth-link-stack-centered">
            <button type="submit" class="auth-submit btn-loading">
                <span class="btn-text">Send reset link</span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Sending...
                </span>
            </button>

            <a href="{{ route('login') }}" class="auth-link">Back to sign in</a>
        </div>
    </form>
@endsection

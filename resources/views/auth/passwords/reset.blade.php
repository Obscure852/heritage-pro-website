@extends('layouts.auth')

@section('title', 'Set Password')
@section('auth_heading', 'Choose your password')
@section('auth_copy', 'Create a password for your CRM account. After this step, first-time users will continue into the short onboarding flow before CRM access is unlocked.')
@section('auth_media_heading', 'Finish account setup securely')
@section('auth_media_copy', 'Use a strong password that only you know. After saving it, the CRM will guide you through the remaining profile setup steps if they are still outstanding.')

@section('auth_inline_email_errors', '1')

@section('auth_content')
    <form method="POST" action="{{ route('password.update') }}" class="auth-form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="auth-field">
            <label for="email">Email address <span class="auth-required">*</span></label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" placeholder="Enter your staff email address" required autocomplete="email" autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password">Password <span class="auth-required">*</span></label>
            <div class="auth-input-shell">
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Create your password" required autocomplete="new-password">
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

        <div class="auth-field">
            <label for="password-confirm">Confirm password <span class="auth-required">*</span></label>
            <div class="auth-input-shell">
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Confirm your password" required autocomplete="new-password">
                <button
                    type="button"
                    class="auth-input-action"
                    data-password-toggle
                    data-password-target="password-confirm"
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
        </div>

        <button type="submit" class="auth-submit btn-loading">
            <span class="btn-text">Save password</span>
            <span class="btn-spinner d-none">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Saving...
            </span>
        </button>
    </form>
@endsection

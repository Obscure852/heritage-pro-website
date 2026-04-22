@extends('layouts.auth')

@section('title', 'Complete Your Profile')
@section('auth_hide_media', '1')
@section('auth_kicker', 'Step 1 of 2')
@section('auth_heading', 'Confirm your identity details')
@section('auth_copy', 'Finish this short setup before CRM access is unlocked. Start with the details your team uses to identify and contact you.')
@section('auth_media_kicker', 'Profile Setup')
@section('auth_media_heading', 'Set up your staff profile')
@section('auth_media_copy', 'A complete staff profile keeps the CRM directory, reporting lines, and operational workflows accurate from your first session.')

@section('auth_progress')
    <div class="auth-progress-track" aria-hidden="true">
        <span class="auth-progress-bar" style="width: 50%;"></span>
    </div>
    <div class="auth-progress-pills">
        <span class="auth-progress-step is-current">1. Identity</span>
        <span class="auth-progress-step">2. Work details</span>
    </div>
@endsection

@section('auth_helper')
    <div class="auth-helper">
        <strong>Access remains locked until both steps are complete.</strong>
        <span>Use the same email that received your password reset link. You can add a profile photo now or skip it and save the rest of your details first.</span>
    </div>
@endsection

@section('auth_content')
    @php
        $avatarPreviewId = 'onboarding-avatar-preview';
        $avatarFallbackId = 'onboarding-avatar-fallback';
        $avatarInputId = 'onboarding-avatar-input';
        $avatarHiddenId = 'onboarding-avatar-hidden';
        $avatarUrl = old('avatar_cropped_image') ?: ($user->avatar_url ?? null);
        $initials = collect(preg_split('/\s+/', trim((string) old('name', $user->name ?? 'CRM User'))) ?: [])
            ->filter()
            ->take(2)
            ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
            ->implode('');
    @endphp

    <form method="POST" action="{{ route('crm.onboarding.profile.update') }}" class="auth-form" id="crm-onboarding-profile-form">
        @csrf
        @method('PATCH')

        <div class="auth-onboarding-layout">
            <div class="auth-onboarding-main">
                <div class="auth-form-grid">
                    <div class="auth-field">
                        <label for="email">Email address <span class="auth-required">*</span></label>
                        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="email" autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-field">
                        <label for="name">Full name <span class="auth-required">*</span></label>
                        <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autocomplete="name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-field">
                        <label for="phone">Phone <span class="auth-required">*</span></label>
                        <input id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" required autocomplete="tel">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-field">
                        <label for="id_number">ID number <span class="auth-required">*</span></label>
                        <input id="id_number" name="id_number" class="form-control @error('id_number') is-invalid @enderror" value="{{ old('id_number', $user->id_number) }}" required>
                        @error('id_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-field">
                        <label for="date_of_birth">Date of birth <span class="auth-required">*</span></label>
                        <input id="date_of_birth" name="date_of_birth" type="date" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}" required>
                        @error('date_of_birth')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-field">
                        <label for="gender">Gender <span class="auth-required">*</span></label>
                        <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                            <option value="">Select gender</option>
                            @foreach ($genders as $value => $label)
                                <option value="{{ $value }}" @selected(old('gender', $user->gender) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-field full">
                        <label for="nationality">Nationality <span class="auth-required">*</span></label>
                        <input id="nationality" name="nationality" class="form-control @error('nationality') is-invalid @enderror" value="{{ old('nationality', $user->nationality) }}" required>
                        @error('nationality')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <aside class="auth-onboarding-sidebar">
                <div class="auth-avatar-panel">
                    <div class="auth-avatar-copy">
                        <strong>Optional profile photo</strong>
                        <span>Add an optional profile photo for your CRM profile.</span>
                    </div>

                    <label for="{{ $avatarInputId }}" class="auth-avatar-shell">
                        @if ($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="{{ old('name', $user->name ?? 'CRM user') }}" id="{{ $avatarPreviewId }}" class="auth-avatar-image">
                        @else
                            <img src="" alt="{{ old('name', $user->name ?? 'CRM user') }}" id="{{ $avatarPreviewId }}" class="auth-avatar-image d-none">
                        @endif

                        <span id="{{ $avatarFallbackId }}" class="auth-avatar-placeholder {{ $avatarUrl ? 'd-none' : '' }}">
                            <strong>{{ $initials !== '' ? $initials : 'CU' }}</strong>
                            <svg class="auth-avatar-upload-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 8.5A2.5 2.5 0 0 1 6.5 6H8l1.2-1.6A1.5 1.5 0 0 1 10.4 4h3.2a1.5 1.5 0 0 1 1.2.4L16 6h1.5A2.5 2.5 0 0 1 20 8.5v7A2.5 2.5 0 0 1 17.5 18h-11A2.5 2.5 0 0 1 4 15.5v-7Z" stroke-width="1.8" stroke-linejoin="round"/>
                                <circle cx="12" cy="12" r="3.2" stroke-width="1.8"/>
                                <path d="M19 5v4" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="M17 7h4" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </label>

                    <div class="auth-avatar-hint">You can add or change it later from the CRM profile.</div>

                    <input
                        id="{{ $avatarInputId }}"
                        class="auth-file-input"
                        type="file"
                        accept="image/*"
                        data-cropper-input
                        data-cropper-title="Crop profile photo"
                        data-cropper-note="The profile photo is saved as a square image for consistent display across CRM."
                        data-cropper-hidden-target="{{ $avatarHiddenId }}"
                        data-cropper-preview-target="{{ $avatarPreviewId }}"
                        data-cropper-fallback-target="{{ $avatarFallbackId }}"
                    >
                    <input type="hidden" name="avatar_cropped_image" id="{{ $avatarHiddenId }}" value="{{ old('avatar_cropped_image') }}">
                    @error('avatar_cropped_image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </aside>
        </div>

    </form>

    <div class="auth-form-footer">
        <div class="auth-form-footer-copy">
            <p class="auth-meta">You will review your work assignment details next.</p>
        </div>

        <div class="auth-form-footer-actions">
            @if ($canSkipProfile)
                <form method="POST" action="{{ route('crm.onboarding.profile.skip') }}">
                    @csrf
                    <button type="submit" class="auth-ghost-button">Skip</button>
                </form>
            @endif

            <button type="submit" form="crm-onboarding-profile-form" class="auth-submit btn-loading">
                <span class="btn-text">
                    Save and continue
                    <svg class="auth-submit-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12H19" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M12 5L19 12L12 19" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Saving...
                </span>
            </button>
        </div>
    </div>
@endsection

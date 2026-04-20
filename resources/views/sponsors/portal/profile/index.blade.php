@extends('layouts.master-sponsor-portal')
@section('title')
    My Profile - Sponsor Portal
@endsection

@section('css')
<style>
    .profile-container {
        background: white;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .profile-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 28px;
        border-radius: 3px 3px 0 0;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        border: 3px solid rgba(255, 255, 255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 600;
        color: white;
    }

    .profile-body {
        padding: 32px;
    }

    .help-text {
        background: #f8f9fa;
        padding: 12px;
        border-left: 4px solid #3b82f6;
        border-radius: 0 3px 3px 0;
        margin-bottom: 24px;
    }

    .help-text .help-title {
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }

    .help-text .help-content {
        color: #6b7280;
        font-size: 13px;
        line-height: 1.4;
    }

    .section-title {
        font-size: 16px;
        font-weight: 600;
        margin: 24px 0 16px 0;
        color: #1f2937;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
    }

    .section-title:first-of-type {
        margin-top: 0;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
        font-size: 14px;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        font-size: 14px;
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-control:disabled {
        background-color: #f3f4f6;
        cursor: not-allowed;
    }

    .required {
        color: #dc2626;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 20px;
        margin-top: 20px;
        border-top: 1px solid #f3f4f6;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }

    .btn-loading.loading .btn-text {
        display: none;
    }

    .btn-loading.loading .btn-spinner {
        display: inline-flex !important;
        align-items: center;
    }

    .btn-loading:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .password-section {
        background: #fefce8;
        border: 1px solid #fef08a;
        border-radius: 3px;
        padding: 20px;
        margin-top: 24px;
    }

    .password-section .section-title {
        margin-top: 0;
        color: #854d0e;
        border-bottom-color: #fef08a;
    }

    .info-text {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }

    .password-input-wrapper {
        position: relative;
    }

    .password-input-wrapper .form-control {
        padding-right: 40px;
    }

    .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        color: #6b7280;
        font-size: 18px;
        line-height: 1;
        transition: color 0.2s;
    }

    .password-toggle:hover {
        color: #3b82f6;
    }

    .password-toggle:focus {
        outline: none;
    }
</style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Sponsor Portal
        @endslot
        @slot('title')
            My Profile
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-md-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @php
        $nameParts = explode(' ', $sponsor->full_name ?? $sponsor->first_name . ' ' . $sponsor->last_name);
        $initials = '';
        foreach (array_slice($nameParts, 0, 2) as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
    @endphp

    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="d-flex align-items-center gap-4">
                <div class="profile-avatar">
                    {{ $initials }}
                </div>
                <div>
                    <h3 class="mb-1">{{ $sponsor->full_name ?? $sponsor->first_name . ' ' . $sponsor->last_name }}</h3>
                    <p class="mb-0" style="opacity: 0.9;">
                        <i class="bx bx-envelope me-1"></i> {{ $sponsor->email }}
                    </p>
                </div>
            </div>
        </div>

        <div class="profile-body">
            <!-- Help Text -->
            <div class="help-text">
                <div class="help-title">Profile Settings</div>
                <div class="help-content">
                    Update your personal information and manage your account security. Changes to your email will be used for future login and communications.
                </div>
            </div>

            <!-- Profile Information Form -->
            <form method="POST" action="{{ route('sponsor.profile.update') }}" class="needs-validation" novalidate>
                @csrf
                @method('PUT')

                <h3 class="section-title">Personal Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                            id="first_name" name="first_name"
                            value="{{ old('first_name', $sponsor->first_name) }}" required>
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                            id="last_name" name="last_name"
                            value="{{ old('last_name', $sponsor->last_name) }}" required>
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <h3 class="section-title">Contact Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address <span class="required">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                            id="email" name="email"
                            value="{{ old('email', $sponsor->email) }}"
                            data-original="{{ $sponsor->email }}" required>
                        <div class="info-text">This email will be used for login and communications.</div>
                        <div class="email-warning alert alert-warning mt-2 mb-0 py-2 px-3" style="display: none; font-size: 13px;">
                            <i class="bx bx-info-circle me-1"></i>
                            <strong>Warning:</strong> Changing your email will log you out immediately. You will need to log in again with your new email.
                        </div>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="text" class="form-control" id="phone"
                            value="{{ $sponsor->phone ?? 'Not provided' }}" disabled>
                        <div class="info-text">Contact school administration to update phone number.</div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>

            <!-- Password Change Section -->
            <div class="password-section">
                <form method="POST" action="{{ route('sponsor.profile.password') }}" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')

                    <h3 class="section-title">
                        <i class="bx bx-lock-alt me-2"></i>Change Password
                    </h3>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="current_password">Current Password <span class="required">*</span></label>
                            <div class="password-input-wrapper">
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                    id="current_password" name="current_password" required>
                                <button type="button" class="password-toggle" data-target="current_password">
                                    <i class="bx bx-show"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div></div>
                    </div>

                    <div class="form-grid" style="margin-top: 16px;">
                        <div class="form-group">
                            <label class="form-label" for="password">New Password <span class="required">*</span></label>
                            <div class="password-input-wrapper">
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" required minlength="8">
                                <button type="button" class="password-toggle" data-target="password">
                                    <i class="bx bx-show"></i>
                                </button>
                            </div>
                            <div class="info-text">Minimum 8 characters.</div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">Confirm New Password <span class="required">*</span></label>
                            <div class="password-input-wrapper">
                                <input type="password" class="form-control"
                                    id="password_confirmation" name="password_confirmation" required minlength="8">
                                <button type="button" class="password-toggle" data-target="password_confirmation">
                                    <i class="bx bx-show"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-key"></i> Update Password</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Updating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation and loading state
        const forms = document.querySelectorAll('.needs-validation');

        forms.forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();

                    const firstInvalidElement = form.querySelector(':invalid');
                    if (firstInvalidElement) {
                        firstInvalidElement.focus();
                    }
                } else {
                    // Show loading state on submit button
                    const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                }

                form.classList.add('was-validated');
            }, false);
        });

        // Password confirmation validation
        const password = document.getElementById('password');
        const passwordConfirmation = document.getElementById('password_confirmation');

        if (password && passwordConfirmation) {
            passwordConfirmation.addEventListener('input', function() {
                if (password.value !== passwordConfirmation.value) {
                    passwordConfirmation.setCustomValidity('Passwords do not match');
                } else {
                    passwordConfirmation.setCustomValidity('');
                }
            });

            password.addEventListener('input', function() {
                if (passwordConfirmation.value && password.value !== passwordConfirmation.value) {
                    passwordConfirmation.setCustomValidity('Passwords do not match');
                } else {
                    passwordConfirmation.setCustomValidity('');
                }
            });
        }

        // Auto-dismiss alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                const dismissButton = alert.querySelector('.btn-close');
                if (dismissButton) {
                    dismissButton.click();
                }
            }, 5000);
        });

        // Email change warning
        const emailInput = document.getElementById('email');
        const emailWarning = document.querySelector('.email-warning');
        if (emailInput && emailWarning) {
            const originalEmail = emailInput.getAttribute('data-original');
            emailInput.addEventListener('input', function() {
                if (this.value !== originalEmail) {
                    emailWarning.style.display = 'block';
                } else {
                    emailWarning.style.display = 'none';
                }
            });
        }

        // Password visibility toggle
        const passwordToggles = document.querySelectorAll('.password-toggle');
        passwordToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bx-show');
                    icon.classList.add('bx-hide');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bx-hide');
                    icon.classList.add('bx-show');
                }
            });
        });
    });
</script>
@endsection

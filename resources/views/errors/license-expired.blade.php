@extends('layouts.master-without-nav')

@section('title')
    License Error
@endsection

@section('css')
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .license-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
            margin: 0 auto;
        }

        .license-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
            text-align: center;
        }

        .license-header h3 {
            margin: 0 0 8px 0;
            font-size: 22px;
            font-weight: 600;
        }

        .license-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .license-body {
            padding: 32px;
        }

        .error-icon-container {
            display: flex;
            justify-content: center;
            margin-bottom: 24px;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .error-icon i.fa-key {
            font-size: 32px;
            color: #991b1b;
        }

        .error-icon i.fa-ban {
            position: absolute;
            font-size: 20px;
            color: #dc2626;
            bottom: 12px;
            right: 12px;
            background: white;
            border-radius: 50%;
            padding: 2px;
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

        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
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

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
            color: white;
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

        .btn-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
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

        .license-form-toggle {
            display: flex;
            justify-content: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .license-form-container {
            margin-top: 24px;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        @media (max-width: 576px) {
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

        .form-control,
        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-control:disabled {
            background-color: #f3f4f6;
            cursor: not-allowed;
        }

        .form-helper {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .form-check-label {
            font-size: 14px;
            color: #374151;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 24px;
        }

        .error-page-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .alert {
            border-radius: 3px;
            margin-bottom: 16px;
        }

        @media (max-width: 768px) {
            .license-body {
                padding: 24px;
            }

            .license-header {
                padding: 20px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                width: 100%;
                justify-content: center;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('body')

    <body>
    @endsection

    @section('content')
        <div class="error-page-container">
            <div class="license-container">
                <div class="license-header">
                    <h3>License Error</h3>
                    <p>Your license has expired or is invalid</p>
                </div>

                <div class="license-body">
                    @if (session('message'))
                        <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                            <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                            <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        @foreach ($errors->all() as $error)
                            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                                <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endforeach
                    @endif

                    <div class="error-icon-container">
                        <div class="error-icon">
                            <i class="fas fa-key"></i>
                            <i class="fas fa-ban"></i>
                        </div>
                    </div>

                    <div class="help-text">
                        <div class="help-title">License Required</div>
                        <div class="help-content">
                            Your current license has expired or is invalid. Please contact support to renew your license or request an extension below.
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button onclick="history.back()" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Go Back
                        </button>
                        <a href="mailto:techteam@imagelife.co" class="btn btn-primary">
                            <i class="fas fa-envelope-open-text"></i> Contact Support
                        </a>
                    </div>

                    @php
                        use Illuminate\Support\Str;
                        $license = \App\Models\License::where('active', 1)->first();
                        $user = auth()->user();
                        $canCreateLicense = false;

                        if ($user && $user->email) {
                            $isHeritageEmail = Str::endsWith($user->email, '@heritagepro.co');
                            $isObscureEmail = $user->email === 'obscure852@gmail.com';
                            $canCreateLicense = $isHeritageEmail || $isObscureEmail;
                        }

                        $licenseName = $license->name ?? '';
                        $canEditLicenseName =
                            empty($licenseName) || Str::contains($licenseName, 'Developer License');
                    @endphp

                    @if ($canCreateLicense)
                        <div class="license-form-toggle">
                            <button class="btn btn-info" onclick="toggleLicenseForm()">
                                <i class="fas fa-key"></i>
                                <span id="toggleBtnText">Show License Form</span>
                            </button>
                        </div>

                        <div id="licenseFormContainer" style="display: none;">
                            <div class="license-form-container">
                                <h3 class="section-title">Create or Extend License</h3>

                                <div class="help-text">
                                    <div class="help-title">Administrator Access</div>
                                    <div class="help-content">
                                        You have permission to create or extend a school license. Fill in the details below to generate a new license.
                                    </div>
                                </div>

                                <form action="{{ route('setup.create-school-license') }}" method="POST" class="needs-validation" novalidate>
                                    @csrf
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="licenseName" class="form-label">License Name</label>
                                            @if ($canEditLicenseName)
                                                <input type="text" name="name" id="licenseName"
                                                    value="{{ $licenseName }}" class="form-control"
                                                    placeholder="Enter license name" required>
                                                <div class="form-helper">The name associated with this license</div>
                                            @else
                                                <input type="text" id="licenseName" value="{{ $licenseName }}"
                                                    class="form-control" disabled>
                                                <input type="text" name="name" value="{{ $licenseName }}" hidden>
                                                <div class="form-helper">License name cannot be changed</div>
                                            @endif
                                        </div>

                                        <div class="form-group">
                                            <label for="licenseYear" class="form-label">Year</label>
                                            <input type="number" name="year" id="licenseYear"
                                                class="form-control" required min="{{ date('Y') }}"
                                                max="{{ date('Y') + 4 }}" placeholder="{{ date('Y') }}">
                                            <div class="form-helper">License year (current to 4 years ahead)</div>
                                        </div>
                                    </div>

                                    <div class="form-grid" style="margin-top: 16px;">
                                        <div class="form-group">
                                            <label for="startDate" class="form-label">Start Date</label>
                                            <input type="date" name="start_date" id="startDate"
                                                class="form-control" required>
                                            <div class="form-helper">When the license becomes active</div>
                                        </div>

                                        <div class="form-group">
                                            <label for="endDate" class="form-label">End Date</label>
                                            <input type="date" name="end_date" id="endDate"
                                                class="form-control" required>
                                            <div class="form-helper">When the license expires</div>
                                        </div>
                                    </div>

                                    <div class="form-grid" style="margin-top: 16px;">
                                        <div class="form-group">
                                            <label for="gracePeriodDays" class="form-label">Grace Period (Days)</label>
                                            <input type="number" name="grace_period_days" id="gracePeriodDays"
                                                value="{{ config('license.grace_period_days', 14) }}"
                                                class="form-control" min="0" max="90">
                                            <div class="form-helper">Days after expiration before degradation begins</div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Status</label>
                                            <div class="form-check form-switch" style="padding-top: 8px;">
                                                <input class="form-check-input" type="checkbox" name="active"
                                                    id="licenseActive" value="1" style="width: 40px; height: 20px;">
                                                <label class="form-check-label" for="licenseActive" style="margin-left: 8px;">Active License</label>
                                            </div>
                                            <div class="form-helper">Toggle to activate this license</div>
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="button" class="btn btn-secondary" onclick="toggleLicenseForm()">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                        <button type="submit" class="btn btn-success btn-loading">
                                            <span class="btn-text"><i class="fas fa-save"></i> Create License</span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                Creating...
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <script>
            function toggleLicenseForm() {
                var container = document.getElementById('licenseFormContainer');
                var toggleBtnText = document.getElementById('toggleBtnText');
                var isHidden = container.style.display === 'none' || container.style.display === '';

                container.style.display = isHidden ? 'block' : 'none';
                toggleBtnText.textContent = isHidden ? 'Hide License Form' : 'Show License Form';
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Form validation with loading state
                const forms = document.querySelectorAll('.needs-validation');

                Array.prototype.slice.call(forms).forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();

                            const firstInvalidElement = form.querySelector(':invalid');
                            if (firstInvalidElement) {
                                firstInvalidElement.focus();
                                firstInvalidElement.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
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
            });
        </script>
    @endsection

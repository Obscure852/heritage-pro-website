@extends('layouts.master')
@section('title')
    Communications Configurations
@endsection
@section('css')
    <style>
        .settings-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 20px;
        }

        .settings-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .settings-body {
            padding: 24px;
        }

        /* Card Border */
        .card {
            border: none;
            box-shadow: none;
        }

        /* Tab Styling */
        .nav-tabs-custom {
            border-bottom: 1px solid #e5e7eb;
            gap: 8px;
            flex-wrap: nowrap;
            min-width: max-content;
        }

        .tabs-scroll {
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            padding-bottom: 4px;
            margin-bottom: 1.5rem;
        }

        .tabs-scroll::-webkit-scrollbar {
            height: 6px;
        }

        .tabs-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        .tabs-scroll .nav-item {
            flex: 0 0 auto;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            background: transparent;
            color: #6b7280;
            font-weight: 500;
            padding: 12px 20px;
            transition: all 0.2s ease;
            border-radius: 0;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #4e73df;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            border-bottom-color: #4e73df;
            background: transparent;
        }

        .nav-tabs-custom .nav-link i {
            font-size: 18px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #4e73df;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-control,
        .form-select,
        textarea.form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus,
        textarea.form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .input-group .form-control {
            border-right: none;
        }

        .input-group .btn-outline-secondary {
            border: 1px solid #d1d5db;
            background: #f9fafb;
            color: #6b7280;
        }

        .input-group .btn-outline-secondary:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .btn {
            padding: 10px 16px;
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

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .btn-sm {
            padding: 8px 14px;
            font-size: 13px;
        }

        /* Section Headers */
        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-header i {
            font-size: 20px;
            color: #4e73df;
        }

        .section-header h5 {
            margin: 0;
            font-weight: 600;
            color: #374151;
            font-size: 16px;
        }

        /* Settings Row */
        .settings-row {
            display: flex;
            align-items: flex-start;
            padding: 16px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .settings-row:last-child {
            border-bottom: none;
        }

        .settings-row .settings-label {
            width: 35%;
            padding-right: 20px;
        }

        .settings-row .settings-label label {
            font-weight: 500;
            color: #374151;
            font-size: 14px;
            margin-bottom: 4px;
            display: block;
        }

        .settings-row .settings-label .settings-hint {
            font-size: 12px;
            color: #9ca3af;
        }

        .settings-row .settings-input {
            flex: 1;
        }

        /* Form Check Switch */
        .form-check-input {
            width: 42px;
            height: 22px;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #4e73df;
            border-color: #4e73df;
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.2);
        }

        /* Tab Pane Animation */
        .tab-pane {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form Section */
        .form-section {
            background: #f9fafb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
        }

        .form-section-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-section-title i {
            color: #4e73df;
        }

        /* Radio Options */
        .radio-option {
            display: flex;
            align-items: center;
            padding: 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .radio-option:hover {
            border-color: #4e73df;
            background: #f0f9ff;
        }

        .radio-option input[type="radio"] {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            cursor: pointer;
        }

        .radio-option input[type="radio"]:checked + .radio-label {
            color: #4e73df;
        }

        .radio-label {
            font-weight: 500;
            color: #374151;
        }

        .template-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
            transition: all 0.2s ease;
            background: #fff;
        }

        .template-card:hover {
            border-color: #4e73df;
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.15);
        }

        .template-card.inactive {
            opacity: 0.7;
            background: #f9fafb;
        }

        .template-name {
            font-weight: 600;
            font-size: 16px;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .template-category {
            display: inline-block;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 500;
            border-radius: 12px;
            background: #e0e7ff;
            color: #4338ca;
            margin-bottom: 8px;
        }

        .template-content {
            font-size: 14px;
            color: #4b5563;
            background: #f9fafb;
            padding: 12px;
            border-radius: 6px;
            margin: 8px 0;
            font-family: 'Courier New', monospace;
        }

        .template-meta {
            font-size: 12px;
            color: #9ca3af;
        }

        .template-stat-card {
            min-width: 150px;
            padding: 14px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
            display: flex;
            flex-direction: column;
        }

        .template-stat-value {
            font-size: 22px;
            font-weight: 700;
            color: #1f2937;
            line-height: 1.2;
        }

        .template-stat-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .placeholder-tag {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-family: monospace;
        }

        .character-counter {
            font-size: 12px;
            color: #6b7280;
        }

        .character-counter.warning {
            color: #f59e0b;
        }

        .character-counter.danger {
            color: #ef4444;
        }

        .guide-step {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
            padding: 18px 20px;
            margin-bottom: 16px;
        }

        .guide-step-number {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #e0e7ff;
            color: #3730a3;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .guide-step-title {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 6px;
        }

        .guide-step-body {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
        }

        .guide-status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .guide-status-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
            padding: 14px 16px;
        }

        .guide-status-label {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .guide-status-value {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
        }

        .guide-status-value.success {
            color: #047857;
        }

        .guide-status-value.warning {
            color: #b45309;
        }

        .guide-code {
            display: block;
            margin-top: 8px;
            padding: 10px 12px;
            background: #111827;
            color: #f9fafb;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            word-break: break-all;
        }

        .guide-links a {
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .settings-row {
                flex-direction: column;
            }

            .settings-row .settings-label {
                width: 100%;
                padding-right: 0;
                margin-bottom: 8px;
            }

            .nav-tabs-custom .nav-link {
                padding: 10px 14px;
                font-size: 13px;
            }
        }

        /* Button loading animation - consistent sizing */
        .btn-loading {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-loading .btn-text {
            display: inline-flex;
            align-items: center;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading .btn-spinner {
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
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('notifications.index') }}">Communications</a>
        @endslot
        @slot('title')
            Settings
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ $error }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="settings-container">
                <div class="settings-header">
                    <h4><i class="fas fa-cog me-2"></i>Communications Settings</h4>
                    <p>Configure email, SMS, WhatsApp, internal messaging, and notification settings for your school</p>
                </div>
                <div class="settings-body">
                    <div class="tabs-scroll">
                        <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start mb-0" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#email-settings" role="tab" id="tab-email-settings">
                                    <i class="bx bxs-envelope"></i>
                                    <span>Email Settings</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#sms-settings" role="tab" id="tab-sms-settings">
                                    <i class="bx bxs-chat"></i>
                                    <span>SMS Settings</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#sms-templates-settings" role="tab" id="tab-sms-templates-settings">
                                    <i class="bx bx-file"></i>
                                    <span>SMS Templates</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#whatsapp-settings" role="tab" id="tab-whatsapp-settings">
                                    <i class="bx bxl-whatsapp"></i>
                                    <span>WhatsApp Settings</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#whatsapp-guide" role="tab" id="tab-whatsapp-guide">
                                    <i class="bx bx-list-check"></i>
                                    <span>WhatsApp Guide</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#api-settings" role="tab" id="tab-api-settings">
                                    <i class="bx bxs-cog"></i>
                                    <span>SMS Provider</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#notification-settings" role="tab" id="tab-notification-settings">
                                    <i class="bx bxs-bell"></i>
                                    <span>Notification Settings</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#internal-messaging-settings" role="tab" id="tab-internal-messaging-settings">
                                    <i class="bx bx-chat"></i>
                                    <span>Internal Messaging</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content">
                        <!-- Email Settings Tab -->
                        <div class="tab-pane" id="email-settings" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title"><i class="fas fa-info-circle me-2"></i>Email Configuration</div>
                                <p class="help-content">Configure email availability, SMTP provider details, and sender identity using the same structured layout as the messaging channels.</p>
                            </div>

                            @php
                                $emailFeatureSetting = ($notificationSettings['feature'] ?? collect())->firstWhere('key', 'features.email_enabled');
                            @endphp

                            @if ($emailFeatureSetting && $emailFeatureSetting->is_editable)
                                <form action="{{ route('setup.update-notification-settings') }}" method="POST" id="emailAvailabilityForm">
                                    @csrf
                                    <div class="form-section">
                                        <div class="section-header">
                                            <i class="fas fa-envelope"></i>
                                            <h5>Email Availability</h5>
                                        </div>
                                        <div class="settings-row">
                                            <div class="settings-label">
                                                <label for="{{ $emailFeatureSetting->key }}">{{ $emailFeatureSetting->display_name }}</label>
                                                @if ($emailFeatureSetting->description)
                                                    <span class="settings-hint">{{ $emailFeatureSetting->description }}</span>
                                                @endif
                                            </div>
                                            <div class="settings-input">
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="settings[{{ $emailFeatureSetting->key }}]" value="0">
                                                    <input class="form-check-input" type="checkbox" id="{{ $emailFeatureSetting->key }}" name="settings[{{ $emailFeatureSetting->key }}]" value="1" {{ $emailFeatureSetting->value ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mb-4">
                                        <button class="btn btn-primary btn-loading" type="submit">
                                            <span class="btn-text"><i class="fas fa-save me-1"></i>Save Email Availability</span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                Saving...
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            @endif

                            <form action="{{ route('setup.email-settings') }}" method="POST">
                                @csrf
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-server"></i>
                                        <h5>SMTP Provider Settings</h5>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label for="mailer">Mail Mailer</label>
                                            <span class="settings-hint">Choose the email transport used by the platform.</span>
                                        </div>
                                        <div class="settings-input">
                                            <select name="MAILER" id="mailer" class="form-select">
                                                <option value="smtp" {{ old('MAILER', $settings['MAILER'] ?? 'smtp') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                                <option value="sendmail" {{ old('MAILER', $settings['MAILER'] ?? 'smtp') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                                <option value="mailgun" {{ old('MAILER', $settings['MAILER'] ?? 'smtp') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                                <option value="ses" {{ old('MAILER', $settings['MAILER'] ?? 'smtp') == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label for="host">Mail Host</label>
                                            <span class="settings-hint">SMTP hostname for your email provider.</span>
                                        </div>
                                        <div class="settings-input">
                                            <input class="form-control" id="host" type="text" name="HOST" value="{{ old('HOST', $settings['HOST'] ?? '') }}" placeholder="smtp.example.com">
                                        </div>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label for="port">Mail Port</label>
                                            <span class="settings-hint">Network port used by the selected mail transport.</span>
                                        </div>
                                        <div class="settings-input">
                                            <input class="form-control" id="port" type="number" name="PORT" value="{{ old('PORT', $settings['PORT'] ?? '465') }}" placeholder="465">
                                        </div>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label for="username">Mail Username</label>
                                            <span class="settings-hint">Login identity used to authenticate with the provider.</span>
                                        </div>
                                        <div class="settings-input">
                                            <input class="form-control" id="username" type="text" name="USERNAME" value="{{ old('USERNAME', $settings['USERNAME'] ?? '') }}" placeholder="email@example.com">
                                        </div>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label for="password_input">Mail Password</label>
                                            <span class="settings-hint">Password or app password used by the mail account.</span>
                                        </div>
                                        <div class="settings-input">
                                            <div class="input-group">
                                                <input class="form-control" id="password_input" type="password" name="PASSWORD" value="{{ old('PASSWORD', $settings['PASSWORD'] ?? '') }}" placeholder="Enter password">
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_input')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label for="encryption">Mail Encryption</label>
                                            <span class="settings-hint">Encryption protocol required by the provider.</span>
                                        </div>
                                        <div class="settings-input">
                                            <select name="ENCRYPTION" id="encryption" class="form-select">
                                                <option value="ssl" {{ old('ENCRYPTION', $settings['ENCRYPTION'] ?? 'ssl') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                                <option value="tls" {{ old('ENCRYPTION', $settings['ENCRYPTION'] ?? 'ssl') == 'tls' ? 'selected' : '' }}>TLS</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-paper-plane"></i>
                                        <h5>Sender Details</h5>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label for="from_address">From Address</label>
                                            <span class="settings-hint">Default sender email address. For AWS WorkMail or SES this should match the mail username.</span>
                                        </div>
                                        <div class="settings-input">
                                            <input class="form-control" id="from_address" type="email" name="FROM_ADDRESS" value="{{ old('FROM_ADDRESS', $settings['FROM_ADDRESS'] ?? '') }}" placeholder="noreply@example.com">
                                        </div>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label for="from_name">From Name</label>
                                            <span class="settings-hint">Display name recipients see on outgoing messages.</span>
                                        </div>
                                        <div class="settings-input">
                                            <input class="form-control" id="from_name" type="text" name="FROM_NAME" value="{{ old('FROM_NAME', $settings['FROM_NAME'] ?? '') }}" placeholder="Your School Name">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button class="btn btn-primary btn-loading" type="submit">
                                        <span class="btn-text"><i class="fas fa-save me-1"></i>Save Email Settings</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- SMS Settings Tab -->
                        <div class="tab-pane" id="sms-settings" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title"><i class="fas fa-info-circle me-2"></i>SMS Configuration</div>
                                <p class="help-content">Configure SMS availability, credits, provider credentials, and pricing in the same structured layout used for WhatsApp.</p>
                            </div>

                            @php
                                $smsFeatureSetting = ($notificationSettings['feature'] ?? collect())->firstWhere('key', 'features.sms_enabled');
                            @endphp

                            @if ($smsFeatureSetting && $smsFeatureSetting->is_editable)
                                <form action="{{ route('setup.update-notification-settings') }}" method="POST" id="smsAvailabilityForm">
                                    @csrf
                                    <div class="form-section">
                                        <div class="section-header">
                                            <i class="fas fa-sms"></i>
                                            <h5>SMS Availability</h5>
                                        </div>
                                        <div class="settings-row">
                                            <div class="settings-label">
                                                <label for="{{ $smsFeatureSetting->key }}">{{ $smsFeatureSetting->display_name }}</label>
                                                @if ($smsFeatureSetting->description)
                                                    <span class="settings-hint">{{ $smsFeatureSetting->description }}</span>
                                                @endif
                                            </div>
                                            <div class="settings-input">
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="settings[{{ $smsFeatureSetting->key }}]" value="0">
                                                    <input class="form-check-input" type="checkbox" id="{{ $smsFeatureSetting->key }}" name="settings[{{ $smsFeatureSetting->key }}]" value="1" {{ $smsFeatureSetting->value ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mb-4">
                                        <button class="btn btn-primary btn-loading" type="submit">
                                            <span class="btn-text"><i class="fas fa-save me-1"></i>Save SMS Availability</span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                Saving...
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            @endif

                            <form action="{{ route('setup.link-sms-update') }}" method="POST">
                                @csrf
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-comment-dots"></i>
                                        <h5>SMS Provider Settings</h5>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label for="sms_credits_package">SMS Credits Package</label>
                                            <span class="settings-hint">Choose the credits package used for SMS sending.</span>
                                        </div>
                                        <div class="settings-input">
                                            <select name="sms_credits_package" id="sms_credits_package" class="form-select">
                                                <option value="">Select SMS Credits ...</option>
                                                @foreach ($smsPackages as $key => $package)
                                                    <option value="{{ $key }}" {{ old('sms_credits_package', $currentPackage) == $key ? 'selected' : '' }}>
                                                        {{ $key }} - {{ $package }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label for="package_amount">Package Amount</label>
                                            <span class="settings-hint">Select the top-up amount for the chosen SMS package.</span>
                                        </div>
                                        <div class="settings-input">
                                            <select name="package_amount" id="package_amount" class="form-select">
                                                <option value="">Select Package Amount ...</option>
                                                <option value="5000" {{ old('package_amount', $packageAmount) == 5000 ? 'selected' : '' }}>5,000.00 BWP</option>
                                                <option value="10000" {{ old('package_amount', $packageAmount) == 10000 ? 'selected' : '' }}>10,000.00 BWP</option>
                                                <option value="20000" {{ old('package_amount', $packageAmount) == 20000 ? 'selected' : '' }}>20,000.00 BWP</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label for="api_key">Link API Key</label>
                                            <span class="settings-hint">Primary credential used to authenticate with the SMS gateway.</span>
                                        </div>
                                        <div class="settings-input">
                                            <div class="input-group">
                                                <input class="form-control" id="api_key" type="password" name="LINK_API_KEY" value="{{ old('LINK_API_KEY', $settingsLink['API_KEY'] ?? '') }}" placeholder="Enter Link API key">
                                                <button class="btn btn-outline-secondary" type="button" onclick="toggleApiKey()">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label for="link_sender_id">Link Sender ID</label>
                                            <span class="settings-hint">Sender name or ID shown to recipients when supported by the provider.</span>
                                        </div>
                                        <div class="settings-input">
                                            <input class="form-control" id="link_sender_id" type="text" name="LINK_SENDER_ID" value="{{ old('LINK_SENDER_ID', $settingsLink['SENDER_ID'] ?? '') }}" placeholder="Enter sender ID">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-coins"></i>
                                        <h5>SMS Package Rates</h5>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label for="sms_rate_basic">Basic Rate (BWP per SMS)</label>
                                            <span class="settings-hint">Unit price charged for the Basic SMS package.</span>
                                        </div>
                                        <div class="settings-input">
                                            <input class="form-control" id="sms_rate_basic" type="number" step="0.01" min="0" name="sms_rate_basic" value="{{ old('sms_rate_basic', $smsRates['basic'] ?? '0.35') }}" placeholder="0.35">
                                        </div>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label for="sms_rate_standard">Standard Rate (BWP per SMS)</label>
                                            <span class="settings-hint">Unit price charged for the Standard SMS package.</span>
                                        </div>
                                        <div class="settings-input">
                                            <input class="form-control" id="sms_rate_standard" type="number" step="0.01" min="0" name="sms_rate_standard" value="{{ old('sms_rate_standard', $smsRates['standard'] ?? '0.30') }}" placeholder="0.30">
                                        </div>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label for="sms_rate_premium">Premium Rate (BWP per SMS)</label>
                                            <span class="settings-hint">Unit price charged for the Premium SMS package.</span>
                                        </div>
                                        <div class="settings-input">
                                            <input class="form-control" id="sms_rate_premium" type="number" step="0.01" min="0" name="sms_rate_premium" value="{{ old('sms_rate_premium', $smsRates['premium'] ?? '0.25') }}" placeholder="0.25">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-arrow-up"></i>
                                        <h5>Package Upgrade</h5>
                                    </div>
                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label for="upgrade_package">Upgrade Package</label>
                                            <span class="settings-hint">Turn this on when you want the selected package amount added to the existing SMS balance.</span>
                                        </div>
                                        <div class="settings-input">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="upgrade_package" id="upgrade_package" role="switch">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-primary btn-loading" type="submit">
                                        <span class="btn-text"><i class="fas fa-save me-1"></i>Save SMS Settings</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane" id="sms-templates-settings" role="tabpanel">
                            @include('settings.partials.sms-templates-tab')
                        </div>

                        <!-- WhatsApp Settings Tab -->
                        <div class="tab-pane" id="whatsapp-settings" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title"><i class="fas fa-info-circle me-2"></i>WhatsApp Configuration</div>
                                <p class="help-content">Configure WhatsApp availability, Twilio credentials, sender details, and template sync settings separately from SMS.</p>
                            </div>

                            @php
                                $whatsappFeatureSetting = ($notificationSettings['feature'] ?? collect())->firstWhere('key', 'features.whatsapp_enabled');
                                $whatsappPlaceholders = [
                                    'whatsapp.account_sid' => 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
                                    'whatsapp.auth_token' => 'Enter your Twilio auth token',
                                    'whatsapp.sender' => 'whatsapp:+14155238886',
                                    'whatsapp.status_webhook_secret' => 'Optional internal secret for status callbacks',
                                    'whatsapp.inbound_webhook_secret' => 'Optional internal secret for inbound callbacks',
                                    'whatsapp.default_language' => 'en',
                                    'whatsapp.template_sync_limit' => '100',
                                ];
                            @endphp

                            <form action="{{ route('setup.update-notification-settings') }}" method="POST" id="whatsappSettingsForm">
                                @csrf

                                @if ($whatsappFeatureSetting && $whatsappFeatureSetting->is_editable)
                                    <div class="form-section">
                                        <div class="section-header">
                                            <i class="fab fa-whatsapp"></i>
                                            <h5>WhatsApp Availability</h5>
                                        </div>
                                        <div class="settings-row">
                                            <div class="settings-label">
                                                <label for="{{ $whatsappFeatureSetting->key }}">{{ $whatsappFeatureSetting->display_name }}</label>
                                                @if ($whatsappFeatureSetting->description)
                                                    <span class="settings-hint">{{ $whatsappFeatureSetting->description }}</span>
                                                @endif
                                            </div>
                                            <div class="settings-input">
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="settings[{{ $whatsappFeatureSetting->key }}]" value="0">
                                                    <input class="form-check-input" type="checkbox" id="{{ $whatsappFeatureSetting->key }}" name="settings[{{ $whatsappFeatureSetting->key }}]" value="1" {{ $whatsappFeatureSetting->value ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fab fa-whatsapp"></i>
                                        <h5>WhatsApp Provider Settings</h5>
                                    </div>
                                    @foreach ($notificationSettings['whatsapp'] ?? [] as $setting)
                                        @if ($setting->is_editable)
                                            <div class="settings-row">
                                                <div class="settings-label">
                                                    <label for="{{ $setting->key }}">{{ $setting->display_name }}</label>
                                                    @if ($setting->description)
                                                        <span class="settings-hint">{{ $setting->description }}</span>
                                                    @endif
                                                </div>
                                                <div class="settings-input">
                                                    @if ($setting->type === 'boolean')
                                                        <div class="form-check form-switch">
                                                            <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                                            <input class="form-check-input" type="checkbox" id="{{ $setting->key }}" name="settings[{{ $setting->key }}]" value="1" {{ $setting->value ? 'checked' : '' }}>
                                                        </div>
                                                    @else
                                                        <input type="{{ $setting->type === 'integer' ? 'number' : ($setting->type === 'password' ? 'password' : 'text') }}" class="form-control" id="{{ $setting->key }}" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" placeholder="{{ $whatsappPlaceholders[$setting->key] ?? 'Enter value' }}" {{ $setting->type === 'decimal' ? 'step=0.01' : '' }}>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <button class="btn btn-secondary" type="button" onclick="window.location.reload()">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                    <button class="btn btn-primary btn-loading" type="submit">
                                        <span class="btn-text"><i class="fas fa-save me-1"></i>Save WhatsApp Settings</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane" id="whatsapp-guide" role="tabpanel">
                            @include('settings.partials.whatsapp-setup-guide')
                        </div>

                        <!-- API Settings Tab -->
                        <div class="tab-pane" id="api-settings" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title"><i class="fas fa-info-circle me-2"></i>SMS Provider Selection</div>
                                <p class="help-content">Choose the SMS gateway used for outbound SMS sends. This tab now follows the same structured configuration layout as the channel tabs.</p>
                            </div>

                            <form id="apiSelectionForm" method="POST" action="{{ route('notifications.store-api-settings') }}">
                                @csrf
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-broadcast-tower"></i>
                                        <h5>SMS Provider</h5>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label>Active Provider</label>
                                            <span class="settings-hint">Select the provider used for all SMS delivery requests across the system.</span>
                                        </div>
                                        <div class="settings-input">
                                            <label class="radio-option mb-0">
                                                <input type="radio" name="sms_api" value="mascom" {{ old('sms_api', $smsApi) === 'mascom' ? 'checked' : '' }}>
                                                <span class="radio-label">Mascom SMS API</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="settings-row">
                                        <div class="settings-label">
                                            <label>Provider Notes</label>
                                            <span class="settings-hint">Provider credentials and pricing are maintained in the `SMS Settings` tab.</span>
                                        </div>
                                        <div class="settings-input">
                                            <div class="form-control bg-light">Current selection will be used by direct SMS and bulk SMS jobs.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save me-1"></i>Save API Selection</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Notification Settings Tab -->
                        <div class="tab-pane" id="notification-settings" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title"><i class="fas fa-info-circle me-2"></i>Notification Preferences</div>
                                <p class="help-content">Configure shared notification settings including delivery limits, email preferences, feature flags, and queue controls.</p>
                            </div>

                            <form action="{{ route('setup.update-notification-settings') }}" method="POST" id="notificationSettingsForm">
                                @csrf

                                <!-- SMS Settings Section -->
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-sms"></i>
                                        <h5>SMS Settings</h5>
                                    </div>
                                    @foreach ($notificationSettings['sms'] ?? [] as $setting)
                                        @if ($setting->is_editable)
                                            <div class="settings-row">
                                                <div class="settings-label">
                                                    <label for="{{ $setting->key }}">{{ $setting->display_name }}</label>
                                                    @if ($setting->description)
                                                        <span class="settings-hint">{{ $setting->description }}</span>
                                                    @endif
                                                </div>
                                            <div class="settings-input">
                                                @if ($setting->type === 'boolean')
                                                    <div class="form-check form-switch">
                                                        <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                                        <input class="form-check-input" type="checkbox" id="{{ $setting->key }}" name="settings[{{ $setting->key }}]" value="1" {{ $setting->value ? 'checked' : '' }}>
                                                    </div>
                                                @else
                                                    <input type="{{ $setting->type === 'integer' ? 'number' : ($setting->type === 'password' ? 'password' : 'text') }}" class="form-control" id="{{ $setting->key }}" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" {{ $setting->type === 'decimal' ? 'step=0.01' : '' }}>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                                <!-- Rate Limit Settings Section -->
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-tachometer-alt"></i>
                                        <h5>Rate Limits</h5>
                                    </div>
                                    @foreach ($notificationSettings['rate_limit'] ?? [] as $setting)
                                        @if ($setting->is_editable)
                                            <div class="settings-row">
                                                <div class="settings-label">
                                                    <label for="{{ $setting->key }}">{{ $setting->display_name }}</label>
                                                    @if ($setting->description)
                                                        <span class="settings-hint">{{ $setting->description }}</span>
                                                    @endif
                                                </div>
                                                <div class="settings-input">
                                                    <input type="number" class="form-control" id="{{ $setting->key }}" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}">
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <!-- Email Settings Section -->
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-envelope"></i>
                                        <h5>Email Settings</h5>
                                    </div>
                                    @foreach ($notificationSettings['email'] ?? [] as $setting)
                                        @if ($setting->is_editable)
                                            <div class="settings-row">
                                                <div class="settings-label">
                                                    <label for="{{ $setting->key }}">{{ $setting->display_name }}</label>
                                                    @if ($setting->description)
                                                        <span class="settings-hint">{{ $setting->description }}</span>
                                                    @endif
                                                </div>
                                                <div class="settings-input">
                                                    <input type="number" class="form-control" id="{{ $setting->key }}" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}">
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <!-- Feature Flags Section -->
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-flag"></i>
                                        <h5>Features</h5>
                                    </div>
                                    @foreach ($notificationSettings['feature'] ?? [] as $setting)
                                        @if (in_array($setting->key, ['features.sms_enabled', 'features.whatsapp_enabled', 'features.email_enabled', 'features.staff_direct_messages_enabled', 'features.staff_presence_launcher_enabled'], true))
                                            @continue
                                        @endif
                                        @if ($setting->is_editable)
                                            <div class="settings-row">
                                                <div class="settings-label">
                                                    <label for="{{ $setting->key }}">{{ $setting->display_name }}</label>
                                                    @if ($setting->description)
                                                        <span class="settings-hint">{{ $setting->description }}</span>
                                                    @endif
                                                </div>
                                                <div class="settings-input">
                                                    <div class="form-check form-switch">
                                                        <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                                        <input class="form-check-input" type="checkbox" id="{{ $setting->key }}" name="settings[{{ $setting->key }}]" value="1" {{ $setting->value ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <!-- Queue Settings Section -->
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-layer-group"></i>
                                        <h5>Queue & Job Settings</h5>
                                    </div>
                                    @foreach ($notificationSettings['queue'] ?? [] as $setting)
                                        @if ($setting->is_editable)
                                            <div class="settings-row">
                                                <div class="settings-label">
                                                    <label for="{{ $setting->key }}">{{ $setting->display_name }}</label>
                                                    @if ($setting->description)
                                                        <span class="settings-hint">{{ $setting->description }}</span>
                                                    @endif
                                                </div>
                                                <div class="settings-input">
                                                    <input type="number" class="form-control" id="{{ $setting->key }}" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}">
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <button class="btn btn-secondary" type="button" onclick="window.location.reload()">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                    <button class="btn btn-primary btn-loading" type="submit">
                                        <span class="btn-text"><i class="fas fa-save me-1"></i>Save Settings</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane" id="internal-messaging-settings" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title"><i class="fas fa-info-circle me-2"></i>Internal Messaging</div>
                                <p class="help-content">Configure the staff-only direct messaging feature and the quiet online-staff launcher. The launcher stays collapsed and silent by default so users can ignore it or click it only when needed.</p>
                            </div>

                            <form action="{{ route('setup.update-notification-settings') }}" method="POST">
                                @csrf

                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-toggle-on"></i>
                                        <h5>Availability</h5>
                                    </div>
                                    @foreach (($notificationSettings['feature'] ?? collect())->whereIn('key', ['features.staff_direct_messages_enabled', 'features.staff_presence_launcher_enabled']) as $setting)
                                        @if ($setting->is_editable)
                                            <div class="settings-row">
                                                <div class="settings-label">
                                                    <label for="{{ $setting->key }}">{{ $setting->display_name }}</label>
                                                    @if ($setting->description)
                                                        <span class="settings-hint">{{ $setting->description }}</span>
                                                    @endif
                                                </div>
                                                <div class="settings-input">
                                                    <div class="form-check form-switch">
                                                        <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                                        <input class="form-check-input" type="checkbox" id="{{ $setting->key }}" name="settings[{{ $setting->key }}]" value="1" {{ $setting->value ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-sliders-h"></i>
                                        <h5>Presence Behavior</h5>
                                    </div>
                                    @foreach ($notificationSettings['internal_messaging'] ?? [] as $setting)
                                        @if ($setting->is_editable)
                                            <div class="settings-row">
                                                <div class="settings-label">
                                                    <label for="{{ $setting->key }}">{{ $setting->display_name }}</label>
                                                    @if ($setting->description)
                                                        <span class="settings-hint">{{ $setting->description }}</span>
                                                    @endif
                                                </div>
                                                <div class="settings-input">
                                                    <input type="number" class="form-control" id="{{ $setting->key }}" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" min="1">
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-mouse-pointer"></i>
                                        <h5>Launcher Behavior</h5>
                                    </div>
                                    <div class="help-text mb-0">
                                        <div class="help-title"><i class="fas fa-check-circle me-2"></i>Designed to stay out of the way</div>
                                        <p class="help-content">The online-staff launcher remains collapsed by default, shows no modal, raises no toast, and never auto-opens. Staff can safely ignore it or click it only when they need to send a direct message.</p>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <button class="btn btn-secondary" type="button" onclick="window.location.reload()">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                    <button class="btn btn-primary btn-loading" type="submit">
                                        <span class="btn-text"><i class="fas fa-save me-1"></i>Save Internal Messaging Settings</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function() {
            'use strict';

            document.addEventListener('DOMContentLoaded', function() {
                initializeTabs();
                initializePasswordToggles();
                initializeApiForm();
                initializeSmsTemplateManagement();
            });

            function initializeTabs() {
                const STORAGE_KEY = 'comm_config_active_tab';

                let activeTab = sessionStorage.getItem(STORAGE_KEY) || 'email-settings';

                const hashTab = window.location.hash.substring(1);
                if (hashTab && isValidTab(hashTab)) {
                    activeTab = hashTab;
                    history.replaceState(null, null, window.location.pathname + window.location.search);
                }

                activateTab(activeTab, false);

                document.querySelectorAll('.nav-link[data-bs-toggle="tab"]').forEach(tab => {
                    tab.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        const tabId = this.getAttribute('href').substring(1);
                        activateTab(tabId, true);
                    });
                });

                document.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        const currentTab = getCurrentActiveTab();
                        if (currentTab) {
                            sessionStorage.setItem(STORAGE_KEY, currentTab);
                        }
                    });
                });

                function isValidTab(tabId) {
                    return ['email-settings', 'sms-settings', 'sms-templates-settings', 'whatsapp-settings', 'whatsapp-guide', 'api-settings', 'notification-settings', 'internal-messaging-settings'].includes(tabId);
                }

                function activateTab(tabId, saveState = true) {
                    if (!isValidTab(tabId)) return;

                    document.querySelectorAll('.nav-link[data-bs-toggle="tab"]').forEach(tab => {
                        tab.classList.remove('active', 'show');
                    });
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('active', 'show');
                    });

                    const selectedTab = document.querySelector(`a[href="#${tabId}"]`);
                    const selectedPane = document.getElementById(tabId);

                    if (selectedTab && selectedPane) {
                        selectedTab.classList.add('active');
                        selectedPane.classList.add('active', 'show');

                        if (saveState) {
                            sessionStorage.setItem(STORAGE_KEY, tabId);
                        }
                    }
                }

                function getCurrentActiveTab() {
                    const activeLink = document.querySelector('.nav-link.active[data-bs-toggle="tab"]');
                    if (activeLink) {
                        const href = activeLink.getAttribute('href');
                        return href ? href.substring(1) : null;
                    }
                    return null;
                }
            }

            function initializePasswordToggles() {
                window.togglePassword = function(inputId) {
                    const input = document.getElementById(inputId);
                    if (input) {
                        const button = input.parentElement.querySelector('button');
                        const icon = button ? button.querySelector('i') : null;

                        if (input.type === 'password') {
                            input.type = 'text';
                            if (icon) {
                                icon.classList.remove('fa-eye');
                                icon.classList.add('fa-eye-slash');
                            }
                        } else {
                            input.type = 'password';
                            if (icon) {
                                icon.classList.remove('fa-eye-slash');
                                icon.classList.add('fa-eye');
                            }
                        }
                    }
                };

                window.toggleApiKey = function() {
                    const apiKey = document.getElementById('api_key');
                    if (apiKey) {
                        const button = apiKey.parentElement.querySelector('button');
                        const icon = button ? button.querySelector('i') : null;

                        if (apiKey.type === 'password') {
                            apiKey.type = 'text';
                            if (icon) {
                                icon.classList.remove('fa-eye');
                                icon.classList.add('fa-eye-slash');
                            }
                        } else {
                            apiKey.type = 'password';
                            if (icon) {
                                icon.classList.remove('fa-eye-slash');
                                icon.classList.add('fa-eye');
                            }
                        }
                    }
                };
            }

            function initializeApiForm() {
                const form = document.getElementById('apiSelectionForm');

                if (form) {
                    form.addEventListener('submit', function(e) {
                        const radios = form.querySelectorAll('input[name="sms_api"]');
                        let selected = false;

                        radios.forEach(radio => {
                            if (radio.checked) {
                                selected = true;
                            }
                        });

                        if (!selected) {
                            e.preventDefault();
                            e.stopPropagation();

                            if (typeof toastr !== 'undefined') {
                                toastr.warning('Please select an SMS API provider');
                            } else {
                                alert('Please select an SMS API provider');
                            }
                        }
                    });
                }
            }

            function initializeSmsTemplateManagement() {
                const smsTemplates = @json($smsTemplates->items());
                let smsTemplateSearchTimeout;

                window.updateSmsTemplateCharacterCount = function(textarea, counterId) {
                    const count = textarea.value.length;
                    const smsUnits = Math.ceil(count / 160) || 0;
                    const counter = document.getElementById(counterId);

                    if (!counter) {
                        return;
                    }

                    counter.textContent = `${count}/480 characters (${smsUnits} SMS)`;
                    counter.classList.remove('warning', 'danger');

                    if (count > 400) {
                        counter.classList.add('danger');
                    } else if (count > 320) {
                        counter.classList.add('warning');
                    }
                };

                window.filterSmsTemplates = function() {
                    const category = document.getElementById('smsTemplateCategoryFilter')?.value || '';
                    const search = document.getElementById('smsTemplateSearchFilter')?.value || '';
                    const url = new URL('{{ route('setup.communications-setup') }}', window.location.origin);

                    if (category) {
                        url.searchParams.set('sms_template_category', category);
                    }

                    if (search) {
                        url.searchParams.set('sms_template_search', search);
                    }

                    url.hash = 'sms-templates-settings';
                    window.location.href = url.toString();
                };

                window.debounceSmsTemplateSearch = function() {
                    clearTimeout(smsTemplateSearchTimeout);
                    smsTemplateSearchTimeout = setTimeout(window.filterSmsTemplates, 400);
                };

                window.resetSmsTemplateFilters = function() {
                    const url = new URL('{{ route('setup.communications-setup') }}', window.location.origin);
                    url.hash = 'sms-templates-settings';
                    window.location.href = url.toString();
                };

                window.smsTemplateEdit = function(id) {
                    const template = smsTemplates.find(item => item.id === id);
                    if (!template) {
                        return;
                    }

                    document.getElementById('editSmsTemplateName').value = template.name;
                    document.getElementById('editSmsTemplateCategory').value = template.category;
                    document.getElementById('editSmsTemplateContent').value = template.content;
                    document.getElementById('editSmsTemplateDescription').value = template.description || '';
                    document.getElementById('editTemplateForm').action = `{{ url('notifications/sms-templates') }}/${id}`;

                    window.updateSmsTemplateCharacterCount(document.getElementById('editSmsTemplateContent'), 'editSmsTemplateCharCount');

                    new bootstrap.Modal(document.getElementById('editTemplateModal')).show();
                };

                window.smsTemplateToggle = function(id) {
                    fetch(`{{ url('notifications/sms-templates') }}/${id}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        }
                    });
                };

                window.smsTemplateDelete = function(id) {
                    if (!confirm('Are you sure you want to delete this template?')) {
                        return;
                    }

                    fetch(`{{ url('notifications/sms-templates') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        }
                    });
                };
            }

            window.addEventListener('beforeunload', function() {
                document.querySelectorAll('.nav-link[data-bs-toggle="tab"]').forEach(tab => {
                    tab.replaceWith(tab.cloneNode(true));
                });
            });

            // Initialize tooltips for notification settings
            if (typeof bootstrap !== 'undefined') {
                const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
            }

            // Handle form submit loading states
            function initializeFormLoadingStates() {
                document.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                        if (submitBtn) {
                            submitBtn.classList.add('loading');
                            submitBtn.disabled = true;
                        }
                    });
                });
            }

            initializeFormLoadingStates();

        })();
    </script>
@endsection

@extends('layouts.master')
@section('title')
    Add Device
@endsection
@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
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

        .required {
            color: #dc2626;
        }

        .text-danger {
            color: #dc2626;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
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

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
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

        .form-check {
            padding-left: 1.75rem;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin-top: 0;
            margin-left: -1.75rem;
        }

        .form-check-label {
            padding-top: 2px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
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
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('staff-attendance.devices.index') }}">Staff Attendance</a>
        @endslot
        @slot('li_2')
            <a class="text-muted font-size-14" href="{{ route('staff-attendance.devices.index') }}">Devices</a>
        @endslot
        @slot('title')
            Add Device
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

    <div class="form-container">
        <div class="page-header">
            <h1 class="page-title">Add Attendance Device</h1>
        </div>

        <div class="help-text">
            <div class="help-title">Configure New Device</div>
            <div class="help-content">
                Enter the connection details for your biometric attendance device. You will need the device's IP address,
                port number, and authentication credentials. Fields marked with <span class="text-danger">*</span> are required.
            </div>
        </div>

        <form class="needs-validation" method="post" action="{{ route('staff-attendance.devices.store') }}" novalidate>
            @csrf

            <h3 class="section-title">Device Information</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="name">Device Name <span class="text-danger">*</span></label>
                    <input type="text"
                        class="form-control @error('name') is-invalid @enderror"
                        name="name" id="name" placeholder="Main Entrance Reader"
                        value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="type">Device Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('type') is-invalid @enderror"
                        name="type" id="type" required>
                        <option value="hikvision" {{ old('type', 'hikvision') == 'hikvision' ? 'selected' : '' }}>Hikvision</option>
                        <option value="zkteco" {{ old('type') == 'zkteco' ? 'selected' : '' }}>ZKTeco</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="serial_number">Serial Number</label>
                    <input type="text"
                        class="form-control @error('serial_number') is-invalid @enderror"
                        name="serial_number" id="serial_number" placeholder="SN123456789"
                        value="{{ old('serial_number') }}">
                    @error('serial_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid" style="margin-top: 16px;">
                <div class="form-group">
                    <label class="form-label" for="location">Location</label>
                    <input type="text"
                        class="form-control @error('location') is-invalid @enderror"
                        name="location" id="location" placeholder="Front Gate, Admin Building, etc."
                        value="{{ old('location') }}">
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h3 class="section-title">Connectivity Mode</h3>
            <div class="help-text" style="margin-bottom: 16px;">
                <div class="help-title">Choose How Events Are Collected</div>
                <div class="help-content">
                    <strong>Pull:</strong> Server connects to device to fetch events (requires network access to device).<br>
                    <strong>Push:</strong> Device sends events to a webhook URL (best for Hikvision with ISUP).<br>
                    <strong>Agent:</strong> Local sync agent at school pushes events to cloud (most flexible).
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="connectivity_mode">Connectivity Mode <span class="text-danger">*</span></label>
                    <select class="form-select @error('connectivity_mode') is-invalid @enderror"
                        name="connectivity_mode" id="connectivity_mode" required onchange="toggleConnectivityFields()">
                        <option value="pull" {{ old('connectivity_mode', 'pull') == 'pull' ? 'selected' : '' }}>Pull - Server fetches from device</option>
                        <option value="push" {{ old('connectivity_mode') == 'push' ? 'selected' : '' }}>Push - Device posts to webhook</option>
                        <option value="agent" {{ old('connectivity_mode') == 'agent' ? 'selected' : '' }}>Agent - Local agent syncs to cloud</option>
                    </select>
                    @error('connectivity_mode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h3 class="section-title">Connection Settings</h3>
            <div class="form-grid" id="pull-mode-fields">
                <div class="form-group">
                    <label class="form-label" for="ip_address">IP Address <span class="text-danger pull-required">*</span></label>
                    <input type="text"
                        class="form-control @error('ip_address') is-invalid @enderror"
                        name="ip_address" id="ip_address" placeholder="192.168.1.100"
                        value="{{ old('ip_address') }}">
                    @error('ip_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="port">Port <span class="text-danger pull-required">*</span></label>
                    <input type="number"
                        class="form-control @error('port') is-invalid @enderror"
                        name="port" id="port" placeholder="80"
                        value="{{ old('port', 80) }}" min="1" max="65535">
                    @error('port')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="timezone">Timezone <span class="text-danger">*</span></label>
                    <select class="form-select @error('timezone') is-invalid @enderror"
                        name="timezone" id="timezone" required>
                        @foreach ($timezones as $tz)
                            <option value="{{ $tz }}" {{ old('timezone', 'Africa/Gaborone') == $tz ? 'selected' : '' }}>
                                {{ $tz }}
                            </option>
                        @endforeach
                    </select>
                    @error('timezone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid" style="margin-top: 16px;" id="auth-fields">
                <div class="form-group">
                    <label class="form-label" for="username">Username <span class="text-danger pull-required">*</span></label>
                    <input type="text"
                        class="form-control @error('username') is-invalid @enderror"
                        name="username" id="username" placeholder="admin"
                        value="{{ old('username') }}">
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password <span class="text-danger pull-required">*</span></label>
                    <input type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        name="password" id="password" placeholder="Enter device password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div id="push-mode-info" style="display: none; margin-top: 16px;">
                <div class="help-text" style="border-left-color: #10b981;">
                    <div class="help-title">Webhook Configuration</div>
                    <div class="help-content">
                        After saving, you'll receive a webhook URL to configure on your Hikvision device.
                        The device will push access control events to this URL automatically.
                    </div>
                </div>
            </div>

            <div id="agent-mode-info" style="display: none; margin-top: 16px;">
                <div class="help-text" style="border-left-color: #f59e0b;">
                    <div class="help-title">Agent Setup Required</div>
                    <div class="help-content">
                        After saving, you'll receive an API endpoint and instructions for setting up
                        the on-premise sync agent. The agent runs locally at your school and pushes
                        events to the cloud.
                    </div>
                </div>
            </div>

            <h3 class="section-title">Status</h3>
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        <strong>Active</strong> - Enable automatic synchronization for this device
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('staff-attendance.devices.index') }}">
                    <i class="bx bx-x"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Add Device</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeFormValidation();
            initializeAlertDismissal();
            toggleConnectivityFields();
        });

        function toggleConnectivityFields() {
            const mode = document.getElementById('connectivity_mode').value;
            const pullFields = document.getElementById('pull-mode-fields');
            const authFields = document.getElementById('auth-fields');
            const pushInfo = document.getElementById('push-mode-info');
            const agentInfo = document.getElementById('agent-mode-info');
            const pullRequiredMarks = document.querySelectorAll('.pull-required');

            // IP, port, username, password fields
            const ipField = document.getElementById('ip_address');
            const portField = document.getElementById('port');
            const usernameField = document.getElementById('username');
            const passwordField = document.getElementById('password');

            // Reset visibility
            pushInfo.style.display = 'none';
            agentInfo.style.display = 'none';

            if (mode === 'pull') {
                // Pull mode: Show all connection fields, make them required
                pullFields.style.display = 'grid';
                authFields.style.display = 'grid';
                pullRequiredMarks.forEach(el => el.style.display = 'inline');
                ipField.required = true;
                portField.required = true;
                usernameField.required = true;
                passwordField.required = true;
            } else if (mode === 'push') {
                // Push mode: IP/port optional (for reference), no auth needed
                pullFields.style.display = 'grid';
                authFields.style.display = 'none';
                pushInfo.style.display = 'block';
                pullRequiredMarks.forEach(el => el.style.display = 'none');
                ipField.required = false;
                portField.required = false;
                usernameField.required = false;
                passwordField.required = false;
            } else if (mode === 'agent') {
                // Agent mode: IP/port for agent config, no auth needed on server side
                pullFields.style.display = 'grid';
                authFields.style.display = 'grid';
                agentInfo.style.display = 'block';
                pullRequiredMarks.forEach(el => el.style.display = 'none');
                ipField.required = false;
                portField.required = false;
                usernameField.required = false;
                passwordField.required = false;
            }
        }

        function initializeFormValidation() {
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
        }

        function initializeAlertDismissal() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    }
                }, 5000);
            });
        }

        // Prevent form resubmission on back/forward navigation
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
@endsection

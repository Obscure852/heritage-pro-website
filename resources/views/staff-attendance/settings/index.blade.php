@extends('layouts.master')
@section('title')
    Staff Attendance Settings
@endsection
@section('css')
    <style>
        .leave-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .leave-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .leave-body {
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

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #3b82f6;
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

        .btn-outline-primary {
            background: transparent;
            border: 1px solid #3b82f6;
            color: #3b82f6;
        }

        .btn-outline-primary:hover {
            background: #3b82f6;
            color: white;
        }

        /* Settings Form */
        .settings-section {
            margin-bottom: 32px;
        }

        .settings-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        /* Button Loading State */
        .btn-loading .btn-spinner {
            display: none;
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

        /* Form Switch Styling */
        .form-check-input:checked {
            background-color: #4e73df;
            border-color: #4e73df;
        }

        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
        }

        @media (max-width: 768px) {
            .leave-header {
                padding: 20px;
            }

            .leave-body {
                padding: 16px;
            }

            .nav-tabs-custom .nav-link {
                padding: 10px 12px;
                font-size: 13px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('staff-attendance.manager.dashboard') }}">Staff Attendance</a>
        @endslot
        @slot('title')
            Staff Attendance Settings
        @endslot
    @endcomponent

    @if (session('success') || session('message'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') ?? session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div id="messageContainer"></div>

    <div class="leave-container">
        <div class="leave-header">
            <h4 class="mb-1 text-white"><i class="fas fa-cog me-2"></i>Staff Attendance Settings</h4>
            <p class="mb-0 opacity-75">Configure working hours, feature toggles, and device integration</p>
        </div>
        <div class="leave-body">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start flex-wrap" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#generalSettings" role="tab">
                                <i class="fas fa-sliders-h me-2 text-muted"></i>General Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#selfService" role="tab">
                                <i class="fas fa-user-clock me-2 text-muted"></i>Self-Service
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#deviceIntegration" role="tab">
                                <i class="fas fa-server me-2 text-muted"></i>Device Integration
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#manualAttendance" role="tab">
                                <i class="fas fa-edit me-2 text-muted"></i>Manual Attendance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#codesTab" role="tab">
                                <i class="fas fa-tags me-2 text-muted"></i>Attendance Codes
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        {{-- General Settings Tab --}}
                        <div class="tab-pane active" id="generalSettings" role="tabpanel">
                            @include('staff-attendance.settings._general-tab')
                        </div>

                        {{-- Self-Service Tab --}}
                        <div class="tab-pane" id="selfService" role="tabpanel">
                            @include('staff-attendance.settings._self-service-tab')
                        </div>

                        {{-- Device Integration Tab --}}
                        <div class="tab-pane" id="deviceIntegration" role="tabpanel">
                            @include('staff-attendance.settings._device-tab')
                        </div>

                        {{-- Manual Attendance Tab --}}
                        <div class="tab-pane" id="manualAttendance" role="tabpanel">
                            @include('staff-attendance.settings._manual-tab')
                        </div>

                        {{-- Attendance Codes Tab --}}
                        <div class="tab-pane" id="codesTab" role="tabpanel">
                            @include('staff-attendance.settings._codes-tab')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab persistence
            const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
            tabLinks.forEach(tabLink => {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    const activeTabHref = event.target.getAttribute('href');
                    localStorage.setItem('staffAttendanceSettingsActiveTab', activeTabHref);
                });
            });

            // Check for hash in URL first
            const hash = window.location.hash;
            if (hash) {
                const tabTriggerEl = document.querySelector(`.nav-link[href="${hash}"]`);
                if (tabTriggerEl) {
                    const tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                    history.replaceState(null, null, window.location.pathname);
                }
            } else {
                // Fall back to localStorage
                const activeTab = localStorage.getItem('staffAttendanceSettingsActiveTab');
                if (activeTab) {
                    const tabTriggerEl = document.querySelector(`.nav-link[href="${activeTab}"]`);
                    if (tabTriggerEl) {
                        const tab = new bootstrap.Tab(tabTriggerEl);
                        tab.show();
                    }
                }
            }

            // Initialize all tab functionalities
            initializeGeneralSettingsTab();
            initializeSelfServiceTab();
            initializeDeviceTab();
            initializeManualTab();
            initializeCodesTab();
            initializeAlertDismissal();
        });

        // Message display function
        function displayMessage(message, type = 'success') {
            const messageContainer = document.getElementById('messageContainer');
            const iconClass = type === 'success' ? 'mdi-check-all' : (type === 'error' ? 'mdi-block-helper' : 'mdi-information');
            messageContainer.innerHTML = `
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-${type === 'error' ? 'danger' : type} alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi ${iconClass} label-icon"></i>
                        <strong>${message}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>`;

            // Scroll to top to show message
            window.scrollTo({ top: 0, behavior: 'smooth' });

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                const alert = messageContainer.querySelector('.alert');
                if (alert) {
                    const dismissBtn = alert.querySelector('.btn-close');
                    if (dismissBtn) dismissBtn.click();
                }
            }, 5000);
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

        // ========================================
        // General Settings Tab Functions
        // ========================================
        function initializeGeneralSettingsTab() {
            const settingsForm = document.getElementById('generalSettingsForm');
            if (settingsForm) {
                settingsForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitSettingsForm(this);
                });
            }
        }

        // ========================================
        // Self-Service Tab Functions
        // ========================================
        function initializeSelfServiceTab() {
            const settingsForm = document.getElementById('selfServiceForm');
            if (settingsForm) {
                settingsForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitSettingsForm(this);
                });
            }
        }

        // ========================================
        // Device Tab Functions
        // ========================================
        function initializeDeviceTab() {
            const triggerSyncBtn = document.getElementById('triggerSyncBtn');
            if (triggerSyncBtn) {
                triggerSyncBtn.addEventListener('click', function() {
                    const deviceSelect = document.getElementById('syncDeviceSelect');
                    const deviceId = deviceSelect ? deviceSelect.value : '';

                    // Show loading state
                    triggerSyncBtn.classList.add('loading');
                    triggerSyncBtn.disabled = true;

                    const syncOutput = document.getElementById('syncOutput');
                    const syncOutputPre = syncOutput ? syncOutput.querySelector('pre') : null;

                    fetch('{{ route("staff-attendance.settings.trigger-sync") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ device_id: deviceId || null })
                    })
                    .then(response => response.json())
                    .then(data => {
                        triggerSyncBtn.classList.remove('loading');
                        triggerSyncBtn.disabled = false;

                        if (syncOutput && syncOutputPre) {
                            syncOutput.classList.remove('d-none');
                            syncOutputPre.textContent = data.output || 'No output';
                        }

                        if (data.success) {
                            displayMessage(data.message);
                        } else {
                            displayMessage(data.message || 'An error occurred during sync.', 'error');
                        }
                    })
                    .catch(error => {
                        triggerSyncBtn.classList.remove('loading');
                        triggerSyncBtn.disabled = false;
                        console.error('Error:', error);
                        displayMessage('An error occurred while triggering sync.', 'error');
                    });
                });
            }
        }

        // ========================================
        // Manual Attendance Tab Functions
        // ========================================
        function initializeManualTab() {
            const settingsForm = document.getElementById('manualAttendanceForm');
            if (settingsForm) {
                settingsForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitSettingsForm(this);
                });
            }
        }

        // ========================================
        // Codes Tab Functions
        // ========================================
        function initializeCodesTab() {
            // Sync color picker with text input for Add modal
            const addColorPicker = document.getElementById('add_color_picker');
            const addColorInput = document.getElementById('add_color');
            if (addColorPicker && addColorInput) {
                addColorPicker.addEventListener('input', function() {
                    addColorInput.value = this.value;
                });
                addColorInput.addEventListener('input', function() {
                    if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                        addColorPicker.value = this.value;
                    }
                });
            }

            // Sync color picker with text input for Edit modal
            const editColorPicker = document.getElementById('edit_color_picker');
            const editColorInput = document.getElementById('edit_color');
            if (editColorPicker && editColorInput) {
                editColorPicker.addEventListener('input', function() {
                    editColorInput.value = this.value;
                });
                editColorInput.addEventListener('input', function() {
                    if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                        editColorPicker.value = this.value;
                    }
                });
            }

            // Loading state for code forms
            const addCodeForm = document.getElementById('addCodeForm');
            const editCodeForm = document.getElementById('editCodeForm');

            if (addCodeForm) {
                addCodeForm.addEventListener('submit', function() {
                    const btn = this.querySelector('button[type="submit"].btn-loading');
                    if (btn) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                });
            }

            if (editCodeForm) {
                editCodeForm.addEventListener('submit', function() {
                    const btn = this.querySelector('button[type="submit"].btn-loading');
                    if (btn) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                });
            }
        }

        // Edit code modal
        function editCode(id, code) {
            const form = document.getElementById('editCodeForm');
            form.action = '{{ url("staff-attendance/codes") }}/' + id;

            document.getElementById('edit_code').value = code.code;
            document.getElementById('edit_name').value = code.name;
            document.getElementById('edit_description').value = code.description || '';
            document.getElementById('edit_color').value = code.color;
            document.getElementById('edit_color_picker').value = code.color;
            document.getElementById('edit_order').value = code.order;
            document.getElementById('edit_counts_as_present').checked = code.counts_as_present;
            document.getElementById('edit_is_active').checked = code.is_active;

            new bootstrap.Modal(document.getElementById('editCodeModal')).show();
        }

        // Delete code confirmation
        function confirmDeleteCode(id, code) {
            Swal.fire({
                title: 'Delete Code?',
                text: 'Are you sure you want to delete "' + code + '"? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-code-form-' + id).submit();
                }
            });
        }

        // ========================================
        // Common Form Submission
        // ========================================
        function submitSettingsForm(form) {
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');

            submitBtn.classList.add('loading');
            submitBtn.disabled = true;

            fetch('{{ route("staff-attendance.settings.update") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;

                if (data.success) {
                    displayMessage(data.message || 'Settings saved successfully.');
                } else {
                    displayMessage(data.message || 'Error saving settings', 'error');
                }
            })
            .catch(error => {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                console.error('Error:', error);
                displayMessage('An error occurred while saving settings', 'error');
            });
        }
    </script>
@endsection

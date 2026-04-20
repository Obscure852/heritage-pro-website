@extends('layouts.master')
@section('title')
    Leave Settings
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

        /* Checkbox Styling */
        .weekend-checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .weekend-checkbox-item {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .weekend-checkbox-item:hover {
            background: #f3f4f6;
        }

        .weekend-checkbox-item input:checked + label {
            color: #4e73df;
            font-weight: 500;
        }

        .weekend-checkbox-item input:checked ~ .weekend-checkbox-item {
            background: #e0e7ff;
            border-color: #4e73df;
        }

        .form-check-input:checked {
            background-color: #4e73df;
            border-color: #4e73df;
        }

        /* Conditional Field */
        .conditional-field {
            padding-left: 24px;
            margin-top: 8px;
        }

        /* Leave Types Table Styles */
        .leave-types-table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .leave-types-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #f3f4f6;
            color: #4b5563;
        }

        .gender-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .gender-male {
            background: #dbeafe;
            color: #1e40af;
        }

        .gender-female {
            background: #fce7f3;
            color: #be185d;
        }

        .gender-all {
            background: #f3f4f6;
            color: #374151;
        }

        .color-indicator {
            width: 16px;
            height: 16px;
            border-radius: 3px;
            display: inline-block;
            vertical-align: middle;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            font-size: 16px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.3;
            margin-bottom: 16px;
        }

        .empty-state h4 {
            color: #374151;
            margin-bottom: 8px;
        }

        .empty-state p {
            margin-bottom: 20px;
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

            .weekend-checkbox-group {
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('leave.balances.dashboard') }}">Leave Management</a>
        @endslot
        @slot('title')
            Leave Settings
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

    @if (session('info'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-information label-icon"></i><strong>{{ session('info') }}</strong>
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
            <h4 class="mb-1 text-white"><i class="fas fa-cog me-2"></i>Leave Settings</h4>
            <p class="mb-0 opacity-75">Configure leave year, weekend days, request rules, and notification settings</p>
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
                            <a class="nav-link" data-bs-toggle="tab" href="#requestRules" role="tab">
                                <i class="fas fa-clipboard-list me-2 text-muted"></i>Request Rules
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#notifications" role="tab">
                                <i class="fas fa-bell me-2 text-muted"></i>Notifications
                            </a>
                        </li>
                        @can('manage-leave-types')
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#leaveTypes" role="tab">
                                <i class="fas fa-calendar-alt me-2 text-muted"></i>Leave Types
                            </a>
                        </li>
                        @endcan
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        {{-- General Settings Tab --}}
                        <div class="tab-pane active" id="generalSettings" role="tabpanel">
                            @include('leave.settings._general-tab')
                        </div>

                        {{-- Request Rules Tab --}}
                        <div class="tab-pane" id="requestRules" role="tabpanel">
                            @include('leave.settings._requests-tab')
                        </div>

                        {{-- Notifications Tab --}}
                        <div class="tab-pane" id="notifications" role="tabpanel">
                            @include('leave.settings._notifications-tab')
                        </div>

                        {{-- Leave Types Tab --}}
                        @can('manage-leave-types')
                        <div class="tab-pane" id="leaveTypes" role="tabpanel">
                            @include('leave.settings._leave-types-tab')
                        </div>
                        @endcan
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
                    localStorage.setItem('leaveSettingsActiveTab', activeTabHref);
                });
            });

            // Check for hash in URL first (e.g., #leaveTypes from redirect)
            const hash = window.location.hash;
            if (hash) {
                const tabTriggerEl = document.querySelector(`.nav-link[href="${hash}"]`);
                if (tabTriggerEl) {
                    const tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                    // Clear the hash to avoid confusion on refresh
                    history.replaceState(null, null, window.location.pathname);
                }
            } else {
                // Fall back to localStorage
                const activeTab = localStorage.getItem('leaveSettingsActiveTab');
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
            initializeRequestRulesTab();
            initializeNotificationsTab();
            initializeLeaveTypesTab();
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

        // Check for update message from localStorage
        const updateMessage = localStorage.getItem('leaveSettingsUpdateMessage');
        if (updateMessage) {
            displayMessage(updateMessage);
            localStorage.removeItem('leaveSettingsUpdateMessage');
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

            // Initialize Balances button
            const initBalancesBtn = document.getElementById('initializeBalancesBtn');
            if (initBalancesBtn) {
                initBalancesBtn.addEventListener('click', function() {
                    if (!confirm('This will create leave balances for all current staff who don\'t already have records for this leave year. Continue?')) {
                        return;
                    }

                    // Show loading state
                    initBalancesBtn.classList.add('loading');
                    initBalancesBtn.disabled = true;

                    fetch('{{ route("leave.settings.initialize-balances") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        initBalancesBtn.classList.remove('loading');
                        initBalancesBtn.disabled = false;

                        if (data.success) {
                            displayMessage(data.message);
                            // Refresh the page to update balance count
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        } else {
                            displayMessage(data.message || 'An error occurred.', 'error');
                        }
                    })
                    .catch(error => {
                        initBalancesBtn.classList.remove('loading');
                        initBalancesBtn.disabled = false;
                        console.error('Error:', error);
                        displayMessage('An error occurred while initializing balances.', 'error');
                    });
                });
            }
        }

        // ========================================
        // Request Rules Tab Functions
        // ========================================
        function initializeRequestRulesTab() {
            const settingsForm = document.getElementById('requestRulesForm');
            if (settingsForm) {
                settingsForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitSettingsForm(this);
                });
            }

            // Toggle conditional fields
            const backdatedCheckbox = document.getElementById('allowBackdatedRequests');
            const backdatedMaxDays = document.getElementById('backdatedMaxDaysContainer');
            if (backdatedCheckbox && backdatedMaxDays) {
                backdatedCheckbox.addEventListener('change', function() {
                    backdatedMaxDays.style.display = this.checked ? 'block' : 'none';
                });
            }

            const autoCancelCheckbox = document.getElementById('autoCancelPendingEnabled');
            const autoCancelDays = document.getElementById('autoCancelDaysContainer');
            if (autoCancelCheckbox && autoCancelDays) {
                autoCancelCheckbox.addEventListener('change', function() {
                    autoCancelDays.style.display = this.checked ? 'block' : 'none';
                });
            }
        }

        // ========================================
        // Notifications Tab Functions
        // ========================================
        function initializeNotificationsTab() {
            const settingsForm = document.getElementById('notificationsForm');
            if (settingsForm) {
                settingsForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitSettingsForm(this);
                });
            }
        }

        // ========================================
        // Leave Types Tab Functions
        // ========================================
        function initializeLeaveTypesTab() {
            const toggleButtons = document.querySelectorAll('.toggle-status-btn');

            toggleButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const leaveTypeId = this.dataset.id;
                    const currentStatus = this.dataset.status === '1';
                    const action = currentStatus ? 'deactivate' : 'activate';

                    if (!confirm(`Are you sure you want to ${action} this leave type?`)) {
                        return;
                    }

                    // Disable button during request
                    button.disabled = true;

                    fetch(`{{ url('leave/types') }}/${leaveTypeId}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update UI
                            const row = button.closest('tr');
                            const statusBadge = row.querySelector('.status-badge');
                            const icon = button.querySelector('i');

                            if (data.is_active) {
                                statusBadge.className = 'status-badge status-active';
                                statusBadge.textContent = 'Active';
                                icon.className = 'bx bx-pause';
                                button.title = 'Deactivate';
                                button.dataset.status = '1';
                            } else {
                                statusBadge.className = 'status-badge status-inactive';
                                statusBadge.textContent = 'Inactive';
                                icon.className = 'bx bx-play';
                                button.title = 'Activate';
                                button.dataset.status = '0';
                            }

                            displayMessage(data.message);
                        } else {
                            displayMessage(data.message || 'An error occurred.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        displayMessage('An error occurred while updating the status.', 'error');
                    })
                    .finally(() => {
                        button.disabled = false;
                    });
                });
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

            fetch('{{ route("leave.settings.update") }}', {
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

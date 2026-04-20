@extends('layouts.master')
@section('title')
    Fee Setup
@endsection
@section('css')
    <style>
        .fee-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .fee-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .fee-body {
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

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-light {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-light:hover {
            background: #e9ecef;
            color: #495057;
        }

        /* Table Styling */
        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        /* Action Buttons (Table) */
        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .action-buttons .btn i {
            font-size: 18px;
        }

        /* Status & Category Badges */
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #fee2e2; color: #991b1b; }
        .status-optional { background: #dbeafe; color: #1e40af; }
        .status-required { background: #fef3c7; color: #92400e; }

        .category-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .category-tuition { background: #e0e7ff; color: #3730a3; }
        .category-boarding { background: #fce7f3; color: #9d174d; }
        .category-transport { background: #d1fae5; color: #065f46; }
        .category-uniform { background: #fef3c7; color: #92400e; }
        .category-books { background: #e0f2fe; color: #0369a1; }
        .category-activity { background: #f3e8ff; color: #6b21a8; }
        .category-other { background: #f3f4f6; color: #4b5563; }

        .applies-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }

        .applies-all { background: #e0e7ff; color: #3730a3; }
        .applies-tuition { background: #fef3c7; color: #92400e; }

        .grade-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #e0e7ff;
            color: #3730a3;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .term-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #fef3c7;
            color: #92400e;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .year-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #d1fae5;
            color: #065f46;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .year-badge.historical {
            background: #f3f4f6;
            color: #6b7280;
        }

        .amount-cell {
            font-weight: 600;
            color: #059669;
        }

        .percentage-value {
            font-weight: 600;
            color: #059669;
        }

        .locked-row {
            background-color: #f9fafb;
        }

        .locked-icon {
            color: #9ca3af;
            margin-right: 4px;
        }

        /* Modal Styling */
        .modal-header {
            background: #f8f9fa;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 3px 3px 0 0;
        }

        .modal-title {
            font-weight: 600;
            color: #374151;
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

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #d1d5db;
            transition: 0.3s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background-color: #10b981;
        }

        input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }

        .payment-method-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 12px;
        }

        .payment-method-item:last-child {
            margin-bottom: 0;
        }

        .payment-method-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .payment-method-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            border-radius: 8px;
            font-size: 18px;
            color: #4b5563;
        }

        .payment-method-details h6 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #374151;
        }

        .payment-method-details p {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
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

        @media (max-width: 768px) {
            .fee-header {
                padding: 20px;
            }

            .fee-body {
                padding: 16px;
            }

            .nav-tabs-custom .nav-link {
                padding: 10px 12px;
                font-size: 13px;
            }
        }

        /* Audit Trail Styling */
        .action-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .action-create { background: #d1fae5; color: #065f46; }
        .action-update { background: #dbeafe; color: #1e40af; }
        .action-delete { background: #fee2e2; color: #991b1b; }
        .action-void { background: #fee2e2; color: #991b1b; }
        .action-cancel { background: #fee2e2; color: #991b1b; }
        .action-issue { background: #f3e8ff; color: #6b21a8; }
        .action-carryover { background: #fef3c7; color: #92400e; }

        .type-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            background: #f3f4f6;
            color: #4b5563;
        }

        .audit-details {
            font-size: 13px;
        }

        .audit-details .reference {
            font-weight: 600;
            color: #1f2937;
        }

        .audit-details .notes {
            color: #6b7280;
            font-size: 12px;
        }

        .expand-btn {
            width: 28px;
            height: 28px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            border: none;
            border-radius: 4px;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s;
        }

        .expand-btn:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .expand-btn.expanded {
            background: #dbeafe;
            color: #1e40af;
        }

        .changes-panel {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 12px;
            margin-top: 8px;
        }

        .change-row {
            display: flex;
            align-items: flex-start;
            padding: 6px 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }

        .change-row:last-child {
            border-bottom: none;
        }

        .change-field {
            width: 140px;
            flex-shrink: 0;
            font-weight: 600;
            color: #374151;
        }

        .change-old {
            flex: 1;
            color: #991b1b;
            background: #fee2e2;
            padding: 2px 6px;
            border-radius: 3px;
            margin-right: 8px;
            text-decoration: line-through;
        }

        .change-new {
            flex: 1;
            color: #065f46;
            background: #d1fae5;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .change-arrow {
            padding: 0 8px;
            color: #9ca3af;
        }

        #auditLogsTable tbody tr.expanded-row {
            background: #f9fafb;
        }

        .ip-address {
            font-family: monospace;
            font-size: 11px;
            color: #6b7280;
        }

        .audit-time {
            font-size: 12px;
        }

        .audit-time .date {
            font-weight: 500;
            color: #374151;
        }

        .audit-time .time {
            color: #6b7280;
            font-size: 11px;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('fees.reports.dashboard') }}">Fee Administration</a>
        @endslot
        @slot('title')
            Fee Setup
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

    <div class="fee-container">
        <div class="fee-header">
            <h4 class="mb-1 text-white"><i class="fas fa-cogs me-2"></i>Fee Setup</h4>
            <p class="mb-0 opacity-75">Configure fee types, structures, discounts, payment methods, and general settings</p>
        </div>
        <div class="fee-body">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start flex-wrap" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#feeTypes" role="tab">
                                <i class="fas fa-tags me-2 text-muted"></i>Fee Types
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#feeStructures" role="tab">
                                <i class="fas fa-layer-group me-2 text-muted"></i>Fee Structures
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#discountTypes" role="tab">
                                <i class="fas fa-percent me-2 text-muted"></i>Discount Types
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#paymentMethods" role="tab">
                                <i class="fas fa-credit-card me-2 text-muted"></i>Payment Methods
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#auditTrail" role="tab">
                                <i class="fas fa-history me-2 text-muted"></i>Audit Trail
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#generalSettings" role="tab">
                                <i class="fas fa-sliders-h me-2 text-muted"></i>General Settings
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        {{-- Fee Types Tab --}}
                        <div class="tab-pane active" id="feeTypes" role="tabpanel">
                            @include('fees.setup._fee-types-tab')
                        </div>

                        {{-- Fee Structures Tab --}}
                        <div class="tab-pane" id="feeStructures" role="tabpanel">
                            @include('fees.setup._fee-structures-tab')
                        </div>

                        {{-- Discount Types Tab --}}
                        <div class="tab-pane" id="discountTypes" role="tabpanel">
                            @include('fees.setup._discount-types-tab')
                        </div>

                        {{-- Payment Methods Tab --}}
                        <div class="tab-pane" id="paymentMethods" role="tabpanel">
                            @include('fees.setup._payment-methods-tab')
                        </div>

                        {{-- Audit Trail Tab --}}
                        <div class="tab-pane" id="auditTrail" role="tabpanel">
                            @include('fees.setup._audit-trail-tab')
                        </div>

                        {{-- General Settings Tab --}}
                        <div class="tab-pane" id="generalSettings" role="tabpanel">
                            @include('fees.setup._general-settings-tab')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Fee Type Modal --}}
    @include('fees.setup._edit-fee-type-modal')

    {{-- Edit Fee Structure Modal --}}
    @include('fees.setup._edit-fee-structure-modal')

    {{-- Copy Structures Modal --}}
    @include('fees.setup._copy-structures-modal')

    {{-- Edit Discount Type Modal --}}
    @include('fees.setup._edit-discount-type-modal')
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab persistence
            const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
            tabLinks.forEach(tabLink => {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    const activeTabHref = event.target.getAttribute('href');
                    localStorage.setItem('feeSetupActiveTab', activeTabHref);
                });
            });

            const activeTab = localStorage.getItem('feeSetupActiveTab');
            if (activeTab) {
                const tabTriggerEl = document.querySelector(`.nav-link[href="${activeTab}"]`);
                if (tabTriggerEl) {
                    const tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                }
            }

            // Initialize all tab functionalities
            initializeFeeTypesTab();
            initializeFeeStructuresTab();
            initializeDiscountTypesTab();
            initializePaymentMethodsTab();
            initializeGeneralSettingsTab();
            initializeAuditTrailTab();
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
        const updateMessage = localStorage.getItem('feeSetupUpdateMessage');
        if (updateMessage) {
            displayMessage(updateMessage);
            localStorage.removeItem('feeSetupUpdateMessage');
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
        // Fee Types Tab Functions
        // ========================================
        function initializeFeeTypesTab() {
            const searchInput = document.getElementById('feeTypeSearchInput');
            const categoryFilter = document.getElementById('feeTypeCategoryFilter');
            const statusFilter = document.getElementById('feeTypeStatusFilter');
            const resetBtn = document.getElementById('feeTypeResetFilters');

            function filterFeeTypeRows() {
                const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
                const categoryValue = categoryFilter ? categoryFilter.value.toLowerCase() : '';
                const statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';

                const rows = document.querySelectorAll('.fee-type-row');

                rows.forEach(row => {
                    const name = row.dataset.name || '';
                    const code = row.dataset.code || '';
                    const category = row.dataset.category || '';
                    const status = row.dataset.status || '';

                    const matchesSearch = !searchTerm || name.includes(searchTerm) || code.includes(searchTerm);
                    const matchesCategory = !categoryValue || category === categoryValue;
                    const matchesStatus = !statusValue || status === statusValue;

                    row.style.display = (matchesSearch && matchesCategory && matchesStatus) ? '' : 'none';
                });
            }

            if (searchInput) searchInput.addEventListener('input', filterFeeTypeRows);
            if (categoryFilter) categoryFilter.addEventListener('change', filterFeeTypeRows);
            if (statusFilter) statusFilter.addEventListener('change', filterFeeTypeRows);

            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    if (searchInput) searchInput.value = '';
                    if (categoryFilter) categoryFilter.value = '';
                    if (statusFilter) statusFilter.value = '';
                    filterFeeTypeRows();
                });
            }

            // Inline add form
            const addFeeTypeForm = document.getElementById('addFeeTypeForm');
            if (addFeeTypeForm) {
                addFeeTypeForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');

                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;

                    fetch('{{ route("fees.setup.types.store") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            localStorage.setItem('feeSetupUpdateMessage', data.message || 'Fee type created successfully.');
                            location.reload();
                        } else {
                            submitBtn.classList.remove('loading');
                            submitBtn.disabled = false;
                            displayMessage(data.message || 'Error creating fee type', 'error');
                        }
                    })
                    .catch(error => {
                        submitBtn.classList.remove('loading');
                        submitBtn.disabled = false;
                        console.error('Error:', error);
                        displayMessage('An error occurred while creating the fee type', 'error');
                    });
                });
            }

            // Edit fee type modal
            const editFeeTypeLinks = document.querySelectorAll('.edit-fee-type');
            editFeeTypeLinks.forEach(link => {
                link.addEventListener('click', function() {
                    document.getElementById('editFeeTypeId').value = this.dataset.id;
                    document.getElementById('editFeeTypeCode').value = this.dataset.code;
                    document.getElementById('editFeeTypeName').value = this.dataset.name;
                    document.getElementById('editFeeTypeCategory').value = this.dataset.category;
                    document.getElementById('editFeeTypeDescription').value = this.dataset.description || '';
                    document.getElementById('editFeeTypeOptional').checked = this.dataset.optional === '1';
                    document.getElementById('editFeeTypeActive').checked = this.dataset.active === '1';
                });
            });

            // Edit fee type form submission
            const editFeeTypeForm = document.getElementById('editFeeTypeForm');
            if (editFeeTypeForm) {
                editFeeTypeForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const feeTypeId = document.getElementById('editFeeTypeId').value;
                    const submitBtn = this.querySelector('button[type="submit"]');

                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;

                    const formData = {
                        code: document.getElementById('editFeeTypeCode').value,
                        name: document.getElementById('editFeeTypeName').value,
                        category: document.getElementById('editFeeTypeCategory').value,
                        description: document.getElementById('editFeeTypeDescription').value,
                        is_optional: document.getElementById('editFeeTypeOptional').checked,
                        is_active: document.getElementById('editFeeTypeActive').checked
                    };

                    fetch(`/fees/setup/fee-types/${feeTypeId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            localStorage.setItem('feeSetupUpdateMessage', data.message || 'Fee type updated successfully.');
                            const modal = bootstrap.Modal.getInstance(document.getElementById('editFeeTypeModal'));
                            modal.hide();
                            location.reload();
                        } else {
                            submitBtn.classList.remove('loading');
                            submitBtn.disabled = false;
                            displayMessage(data.message || 'Error updating fee type', 'error');
                        }
                    })
                    .catch(error => {
                        submitBtn.classList.remove('loading');
                        submitBtn.disabled = false;
                        console.error('Error:', error);
                        displayMessage('An error occurred while updating the fee type', 'error');
                    });
                });
            }
        }

        // ========================================
        // Fee Structures Tab Functions
        // ========================================
        function initializeFeeStructuresTab() {
            const searchInput = document.getElementById('structureSearchInput');
            const gradeFilter = document.getElementById('structureGradeFilter');
            const termFilter = document.getElementById('structureTermFilter');
            const yearFilter = document.getElementById('structureYearFilter');
            const resetBtn = document.getElementById('structureResetFilters');

            function filterStructureRows() {
                const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
                const gradeValue = gradeFilter ? gradeFilter.value.toLowerCase() : '';
                const termValue = termFilter ? termFilter.value : '';
                const yearValue = yearFilter ? yearFilter.value : '';

                const rows = document.querySelectorAll('.fee-structure-row');

                rows.forEach(row => {
                    const feeType = row.dataset.feeType || '';
                    const grade = row.dataset.grade || '';
                    const term = row.dataset.term || '';
                    const year = row.dataset.year || '';

                    const matchesSearch = !searchTerm || feeType.includes(searchTerm);
                    const matchesGrade = !gradeValue || grade === gradeValue;
                    const matchesTerm = !termValue || term === termValue;
                    const matchesYear = !yearValue || year === yearValue;

                    row.style.display = (matchesSearch && matchesGrade && matchesTerm && matchesYear) ? '' : 'none';
                });
            }

            if (searchInput) searchInput.addEventListener('input', filterStructureRows);
            if (gradeFilter) gradeFilter.addEventListener('change', filterStructureRows);
            if (termFilter) termFilter.addEventListener('change', filterStructureRows);
            if (yearFilter) yearFilter.addEventListener('change', filterStructureRows);

            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    if (searchInput) searchInput.value = '';
                    if (gradeFilter) gradeFilter.value = '';
                    if (yearFilter) yearFilter.value = '';
                    filterStructureRows();
                });
            }

            // Apply filter on page load (for pre-selected year)
            filterStructureRows();

            // Edit fee structure modal
            const editStructureLinks = document.querySelectorAll('.edit-fee-structure');
            editStructureLinks.forEach(link => {
                link.addEventListener('click', function() {
                    document.getElementById('editStructureId').value = this.dataset.id;
                    document.getElementById('editStructureFeeType').value = this.dataset.feeTypeId;
                    document.getElementById('editStructureGrade').value = this.dataset.gradeId;
                    document.getElementById('editStructureYear').value = this.dataset.year;
                    document.getElementById('editStructureAmount').value = this.dataset.amount;
                });
            });

            // Edit fee structure form submission
            const editStructureForm = document.getElementById('editFeeStructureForm');
            if (editStructureForm) {
                editStructureForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const structureId = document.getElementById('editStructureId').value;
                    const submitBtn = this.querySelector('button[type="submit"]');

                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;

                    const formData = {
                        fee_type_id: document.getElementById('editStructureFeeType').value,
                        grade_id: document.getElementById('editStructureGrade').value,
                        year: document.getElementById('editStructureYear').value,
                        amount: document.getElementById('editStructureAmount').value
                    };

                    fetch(`/fees/setup/fee-structures/${structureId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            localStorage.setItem('feeSetupUpdateMessage', data.message || 'Fee structure updated successfully.');
                            const modal = bootstrap.Modal.getInstance(document.getElementById('editFeeStructureModal'));
                            modal.hide();
                            location.reload();
                        } else {
                            submitBtn.classList.remove('loading');
                            submitBtn.disabled = false;
                            displayMessage(data.message || 'Error updating fee structure', 'error');
                        }
                    })
                    .catch(error => {
                        submitBtn.classList.remove('loading');
                        submitBtn.disabled = false;
                        console.error('Error:', error);
                        displayMessage('An error occurred while updating the fee structure', 'error');
                    });
                });
            }

            // Copy structures modal
            initializeCopyStructuresModal();
        }

        function initializeCopyStructuresModal() {
            const adjustCheckbox = document.getElementById('copyAdjustAmount');
            const adjustmentContainer = document.getElementById('copyAdjustmentContainer');
            const adjustmentInput = document.getElementById('copyAdjustmentPercentage');
            const copyForm = document.getElementById('copyStructuresForm');
            const sourceTermSelect = document.getElementById('copySourceTerm');
            const destTermSelect = document.getElementById('copyDestTerm');

            if (adjustCheckbox) {
                adjustCheckbox.addEventListener('change', function() {
                    if (adjustmentContainer) {
                        adjustmentContainer.style.display = this.checked ? 'block' : 'none';
                        if (!this.checked && adjustmentInput) {
                            adjustmentInput.value = '';
                        }
                    }
                });
            }

            if (sourceTermSelect && destTermSelect) {
                sourceTermSelect.addEventListener('change', function() {
                    const selectedValue = this.value;
                    Array.from(destTermSelect.options).forEach(option => {
                        option.disabled = option.value === selectedValue && selectedValue !== '';
                    });
                });

                destTermSelect.addEventListener('change', function() {
                    const selectedValue = this.value;
                    Array.from(sourceTermSelect.options).forEach(option => {
                        option.disabled = option.value === selectedValue && selectedValue !== '';
                    });
                });
            }

            if (copyForm) {
                copyForm.addEventListener('submit', function(event) {
                    if (sourceTermSelect && destTermSelect) {
                        if (sourceTermSelect.value === destTermSelect.value) {
                            event.preventDefault();
                            alert('Source and destination terms cannot be the same.');
                            return false;
                        }
                    }

                    const submitBtn = copyForm.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            }
        }

        // ========================================
        // Discount Types Tab Functions
        // ========================================
        function initializeDiscountTypesTab() {
            const searchInput = document.getElementById('discountSearchInput');
            const appliesToFilter = document.getElementById('discountAppliesToFilter');
            const statusFilter = document.getElementById('discountStatusFilter');
            const resetBtn = document.getElementById('discountResetFilters');

            function filterDiscountRows() {
                const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
                const appliesToValue = appliesToFilter ? appliesToFilter.value.toLowerCase() : '';
                const statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';

                const rows = document.querySelectorAll('.discount-type-row');

                rows.forEach(row => {
                    const name = row.dataset.name || '';
                    const code = row.dataset.code || '';
                    const appliesTo = row.dataset.appliesTo || '';
                    const status = row.dataset.status || '';

                    const matchesSearch = !searchTerm || name.includes(searchTerm) || code.includes(searchTerm);
                    const matchesAppliesTo = !appliesToValue || appliesTo === appliesToValue;
                    const matchesStatus = !statusValue || status === statusValue;

                    row.style.display = (matchesSearch && matchesAppliesTo && matchesStatus) ? '' : 'none';
                });
            }

            if (searchInput) searchInput.addEventListener('input', filterDiscountRows);
            if (appliesToFilter) appliesToFilter.addEventListener('change', filterDiscountRows);
            if (statusFilter) statusFilter.addEventListener('change', filterDiscountRows);

            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    if (searchInput) searchInput.value = '';
                    if (appliesToFilter) appliesToFilter.value = '';
                    if (statusFilter) statusFilter.value = '';
                    filterDiscountRows();
                });
            }

            // Inline add form
            const addDiscountForm = document.getElementById('addDiscountTypeForm');
            if (addDiscountForm) {
                addDiscountForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');

                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;

                    fetch('{{ route("fees.setup.discount-types.store") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            localStorage.setItem('feeSetupUpdateMessage', data.message || 'Discount type created successfully.');
                            location.reload();
                        } else {
                            submitBtn.classList.remove('loading');
                            submitBtn.disabled = false;
                            displayMessage(data.message || 'Error creating discount type', 'error');
                        }
                    })
                    .catch(error => {
                        submitBtn.classList.remove('loading');
                        submitBtn.disabled = false;
                        console.error('Error:', error);
                        displayMessage('An error occurred while creating the discount type', 'error');
                    });
                });
            }

            // Edit discount type modal
            const editDiscountLinks = document.querySelectorAll('.edit-discount-type');
            editDiscountLinks.forEach(link => {
                link.addEventListener('click', function() {
                    document.getElementById('editDiscountTypeId').value = this.dataset.id;
                    document.getElementById('editDiscountTypeCode').value = this.dataset.code;
                    document.getElementById('editDiscountTypeName').value = this.dataset.name;
                    document.getElementById('editDiscountTypePercentage').value = this.dataset.percentage;
                    document.getElementById('editDiscountTypeAppliesTo').value = this.dataset.appliesTo;
                    document.getElementById('editDiscountTypeDescription').value = this.dataset.description || '';
                    document.getElementById('editDiscountTypeActive').checked = this.dataset.active === '1';
                });
            });

            // Edit discount type form submission
            const editDiscountForm = document.getElementById('editDiscountTypeForm');
            if (editDiscountForm) {
                editDiscountForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const discountTypeId = document.getElementById('editDiscountTypeId').value;
                    const submitBtn = this.querySelector('button[type="submit"]');

                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;

                    const formData = {
                        code: document.getElementById('editDiscountTypeCode').value,
                        name: document.getElementById('editDiscountTypeName').value,
                        percentage: document.getElementById('editDiscountTypePercentage').value,
                        applies_to: document.getElementById('editDiscountTypeAppliesTo').value,
                        description: document.getElementById('editDiscountTypeDescription').value,
                        is_active: document.getElementById('editDiscountTypeActive').checked
                    };

                    fetch(`/fees/setup/discount-types/${discountTypeId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            localStorage.setItem('feeSetupUpdateMessage', data.message || 'Discount type updated successfully.');
                            const modal = bootstrap.Modal.getInstance(document.getElementById('editDiscountTypeModal'));
                            modal.hide();
                            location.reload();
                        } else {
                            submitBtn.classList.remove('loading');
                            submitBtn.disabled = false;
                            displayMessage(data.message || 'Error updating discount type', 'error');
                        }
                    })
                    .catch(error => {
                        submitBtn.classList.remove('loading');
                        submitBtn.disabled = false;
                        console.error('Error:', error);
                        displayMessage('An error occurred while updating the discount type', 'error');
                    });
                });
            }
        }

        // ========================================
        // Payment Methods Tab Functions
        // ========================================
        function initializePaymentMethodsTab() {
            const toggles = document.querySelectorAll('.payment-method-toggle');
            toggles.forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const methodKey = this.dataset.method;
                    const isEnabled = this.checked;

                    fetch('{{ route("fees.setup.payment-methods.update") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            method: methodKey,
                            enabled: isEnabled
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayMessage(data.message || 'Payment method updated successfully.');
                        } else {
                            // Revert toggle
                            this.checked = !isEnabled;
                            displayMessage(data.message || 'Error updating payment method', 'error');
                        }
                    })
                    .catch(error => {
                        // Revert toggle
                        this.checked = !isEnabled;
                        console.error('Error:', error);
                        displayMessage('An error occurred while updating the payment method', 'error');
                    });
                });
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
                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');

                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;

                    fetch('{{ route("fees.setup.settings.update") }}', {
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
                });
            }
        }

        // Delete confirmation
        function confirmDelete(type) {
            return confirm(`Are you sure you want to delete this ${type}? This action cannot be undone.`);
        }

        // ========================================
        // Audit Trail Tab Functions
        // ========================================
        function initializeAuditTrailTab() {
            let auditLogsLoaded = false;
            let currentPage = 1;

            // Lazy load logs when tab is first shown
            const auditTrailTab = document.querySelector('a[href="#auditTrail"]');
            if (auditTrailTab) {
                auditTrailTab.addEventListener('shown.bs.tab', function() {
                    if (!auditLogsLoaded) {
                        loadAuditLogs();
                        auditLogsLoaded = true;
                    }
                });

                // If audit trail tab is already active (from localStorage), load logs
                if (auditTrailTab.classList.contains('active')) {
                    loadAuditLogs();
                    auditLogsLoaded = true;
                }
            }

            // Filter event handlers
            const applyFiltersBtn = document.getElementById('auditApplyFilters');
            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', function() {
                    currentPage = 1;
                    loadAuditLogs();
                });
            }

            const resetFiltersBtn = document.getElementById('auditResetFilters');
            if (resetFiltersBtn) {
                resetFiltersBtn.addEventListener('click', function() {
                    document.getElementById('auditSearchInput').value = '';
                    document.getElementById('auditDateFrom').value = '';
                    document.getElementById('auditDateTo').value = '';
                    document.getElementById('auditActionFilter').value = '';
                    document.getElementById('auditUserFilter').value = '';
                    document.getElementById('auditTypeFilter').value = '';
                    currentPage = 1;
                    loadAuditLogs();
                });
            }

            // Enter key to apply filters
            const searchInput = document.getElementById('auditSearchInput');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        currentPage = 1;
                        loadAuditLogs();
                    }
                });
            }

            function loadAuditLogs(page = 1) {
                currentPage = page;

                // Show loading state
                document.getElementById('auditInitialState').style.display = 'none';
                document.getElementById('auditLoadingSpinner').style.display = 'block';
                document.getElementById('auditResultsContainer').style.display = 'none';
                document.getElementById('auditEmptyState').style.display = 'none';

                // Build query params
                const params = new URLSearchParams();
                params.append('page', page);

                const search = document.getElementById('auditSearchInput').value;
                if (search) params.append('search', search);

                const dateFrom = document.getElementById('auditDateFrom').value;
                if (dateFrom) params.append('date_from', dateFrom);

                const dateTo = document.getElementById('auditDateTo').value;
                if (dateTo) params.append('date_to', dateTo);

                const action = document.getElementById('auditActionFilter').value;
                if (action) params.append('action', action);

                const userId = document.getElementById('auditUserFilter').value;
                if (userId) params.append('user_id', userId);

                const modelType = document.getElementById('auditTypeFilter').value;
                if (modelType) params.append('model_type', modelType);

                fetch(`{{ route('fees.setup.audit-logs') }}?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('auditLoadingSpinner').style.display = 'none';

                    if (data.success && data.data.length > 0) {
                        renderAuditLogs(data.data);
                        renderPagination(data.pagination);
                        document.getElementById('auditResultsContainer').style.display = 'block';
                    } else {
                        document.getElementById('auditEmptyState').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error loading audit logs:', error);
                    document.getElementById('auditLoadingSpinner').style.display = 'none';
                    displayMessage('Failed to load audit logs. Please try again.', 'error');
                });
            }

            function renderAuditLogs(logs) {
                const tbody = document.getElementById('auditLogsBody');
                tbody.innerHTML = '';

                logs.forEach((log, index) => {
                    const row = document.createElement('tr');
                    row.id = `audit-row-${log.id}`;

                    // Format date/time
                    const createdAt = new Date(log.created_at);
                    const dateStr = createdAt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                    const timeStr = createdAt.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });

                    // Truncate notes
                    const truncatedNotes = log.notes ? (log.notes.length > 50 ? log.notes.substring(0, 50) + '...' : log.notes) : '';

                    row.innerHTML = `
                        <td class="audit-time">
                            <div class="date">${dateStr}</div>
                            <div class="time">${timeStr}</div>
                        </td>
                        <td>${escapeHtml(log.user_name)}</td>
                        <td><span class="action-badge action-${log.action}">${escapeHtml(log.action_label)}</span></td>
                        <td><span class="type-badge">${escapeHtml(log.model_type_display)}</span></td>
                        <td class="audit-details">
                            <div class="reference">${escapeHtml(log.auditable_display)}</div>
                            ${truncatedNotes ? `<div class="notes">${escapeHtml(truncatedNotes)}</div>` : ''}
                        </td>
                        <td><span class="ip-address">${escapeHtml(log.ip_address)}</span></td>
                        <td>
                            ${log.has_changes ? `<button type="button" class="expand-btn" data-log-id="${log.id}" title="View changes">
                                <i class="fas fa-chevron-down"></i>
                            </button>` : ''}
                        </td>
                    `;

                    tbody.appendChild(row);

                    // Add expand row for changes if available
                    if (log.has_changes) {
                        const expandRow = document.createElement('tr');
                        expandRow.id = `audit-expand-${log.id}`;
                        expandRow.style.display = 'none';
                        expandRow.innerHTML = `
                            <td colspan="7">
                                <div class="changes-panel">
                                    ${renderChanges(log.changes)}
                                </div>
                            </td>
                        `;
                        tbody.appendChild(expandRow);
                    }
                });

                // Attach expand button handlers
                document.querySelectorAll('.expand-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const logId = this.dataset.logId;
                        const expandRow = document.getElementById(`audit-expand-${logId}`);
                        const mainRow = document.getElementById(`audit-row-${logId}`);

                        if (expandRow.style.display === 'none') {
                            expandRow.style.display = '';
                            mainRow.classList.add('expanded-row');
                            this.classList.add('expanded');
                            this.querySelector('i').classList.replace('fa-chevron-down', 'fa-chevron-up');
                        } else {
                            expandRow.style.display = 'none';
                            mainRow.classList.remove('expanded-row');
                            this.classList.remove('expanded');
                            this.querySelector('i').classList.replace('fa-chevron-up', 'fa-chevron-down');
                        }
                    });
                });
            }

            function renderChanges(changes) {
                if (!changes || changes.length === 0) {
                    return '<p class="text-muted mb-0">No detailed changes recorded.</p>';
                }

                return changes.map(change => `
                    <div class="change-row">
                        <div class="change-field">${escapeHtml(change.field)}</div>
                        ${change.old_value !== null && change.old_value !== '-' ?
                            `<div class="change-old">${escapeHtml(change.old_value)}</div>` :
                            '<div class="change-old" style="background: transparent; color: #9ca3af;">-</div>'}
                        <div class="change-arrow"><i class="fas fa-arrow-right"></i></div>
                        ${change.new_value !== null && change.new_value !== '-' ?
                            `<div class="change-new">${escapeHtml(change.new_value)}</div>` :
                            '<div class="change-new" style="background: transparent; color: #9ca3af;">-</div>'}
                    </div>
                `).join('');
            }

            function renderPagination(pagination) {
                // Update summary
                const summary = document.getElementById('auditResultsSummary');
                summary.textContent = `Showing ${pagination.from || 0} - ${pagination.to || 0} of ${pagination.total} records`;

                // Update page info
                const pageInfo = document.getElementById('auditPaginationInfo');
                pageInfo.textContent = `Page ${pagination.current_page} of ${pagination.last_page}`;

                // Render pagination buttons
                const container = document.getElementById('auditPaginationButtons');
                container.innerHTML = '';

                // Previous button
                const prevBtn = document.createElement('button');
                prevBtn.type = 'button';
                prevBtn.className = 'btn btn-sm btn-light';
                prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
                prevBtn.disabled = pagination.current_page === 1;
                prevBtn.addEventListener('click', () => loadAuditLogs(pagination.current_page - 1));
                container.appendChild(prevBtn);

                // Page numbers (show max 5 pages)
                const startPage = Math.max(1, pagination.current_page - 2);
                const endPage = Math.min(pagination.last_page, startPage + 4);

                for (let i = startPage; i <= endPage; i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.type = 'button';
                    pageBtn.className = `btn btn-sm ${i === pagination.current_page ? 'btn-primary' : 'btn-light'}`;
                    pageBtn.textContent = i;
                    pageBtn.addEventListener('click', () => loadAuditLogs(i));
                    container.appendChild(pageBtn);
                }

                // Next button
                const nextBtn = document.createElement('button');
                nextBtn.type = 'button';
                nextBtn.className = 'btn btn-sm btn-light';
                nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
                nextBtn.disabled = pagination.current_page === pagination.last_page;
                nextBtn.addEventListener('click', () => loadAuditLogs(pagination.current_page + 1));
                container.appendChild(nextBtn);
            }

            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Expose loadAuditLogs for external calls if needed
            window.loadAuditLogs = loadAuditLogs;
        }
    </script>
@endsection

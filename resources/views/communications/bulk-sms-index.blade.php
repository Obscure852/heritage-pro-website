@extends('layouts.master')
@section('title')
    Communications Module
@endsection
@section('css')
    <style>
        .admissions-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .admissions-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .admissions-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 20px;
        }

        .admissions-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .admissions-body {
            padding: 24px;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .admissions-header {
                padding: 20px;
            }

            .admissions-body {
                padding: 16px;
            }
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #4e73df;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
            font-size: 13px;
            color: #555;
        }

        .term-filter-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 12px;
        }

        .term-filter-wrapper .form-select {
            width: auto;
            min-width: 180px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            padding: 8px 12px;
        }

        .term-filter-wrapper .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        /* Skeleton Loader Styles */
        .skeleton {
            animation: skeleton-loading 1.5s linear infinite alternate;
            border-radius: 4px;
        }

        .skeleton-text {
            width: 100%;
            height: 14px;
            margin-bottom: 0;
            background-color: #e9ecef;
        }

        .skeleton-text.skeleton-xs {
            width: 30%;
            height: 12px;
        }

        .skeleton-text.skeleton-sm {
            width: 40%;
        }

        .skeleton-text.skeleton-md {
            width: 60%;
        }

        .skeleton-text.skeleton-lg {
            width: 80%;
        }

        .skeleton-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background-color: #e9ecef;
        }

        .skeleton-badge {
            width: 60px;
            height: 20px;
            display: inline-block;
            background-color: #e9ecef;
            border-radius: 3px;
        }

        .skeleton-button {
            width: 100px;
            height: 28px;
            display: inline-block;
            background-color: #e9ecef;
            border-radius: 3px;
        }

        .skeleton-card {
            height: 120px;
            background-color: #f8f9fa;
            border-radius: 3px;
        }

        @keyframes skeleton-loading {
            0% {
                background-color: hsl(200, 20%, 90%);
            }

            100% {
                background-color: hsl(200, 20%, 95%);
            }
        }

        /* Modal View Transitions */
        .modal-view {
            display: none;
        }

        .modal-view.active {
            display: block;
        }

        /* Background Job Indicator */
        .background-job-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            z-index: 1040;
            min-width: 300px;
            display: none;
        }

        .background-job-indicator.show {
            display: block;
        }

        /* Modal Theming */
        .modal-content {
            border: none;
            border-radius: 3px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .modal-header .modal-title {
            font-weight: 600;
            font-size: 16px;
            color: #374151;
        }

        .modal-header .btn-close {
            opacity: 0.5;
        }

        .modal-header .btn-close:hover {
            opacity: 1;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-body .form-label {
            font-weight: 500;
            color: #374151;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .modal-body .form-control,
        .modal-body .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            padding: 10px 12px;
        }

        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .modal-body textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .modal-body .alert {
            border-radius: 3px;
            font-size: 13px;
        }

        .modal-body .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        .modal-body .alert-light {
            background: #f9fafb;
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .modal .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 3px;
        }

        .modal .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .modal .btn-primary:disabled {
            background: #9ca3af;
            transform: none;
            box-shadow: none;
        }

        .modal .btn-outline-secondary {
            border-color: #d1d5db;
            color: #6b7280;
            font-size: 13px;
            border-radius: 3px;
        }

        .modal .btn-outline-secondary:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
            color: #374151;
        }

        .modal .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            font-size: 13px;
            border-radius: 3px;
        }

        .modal .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }

        .modal .progress {
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .modal .progress-bar {
            font-size: 12px;
            font-weight: 600;
        }

        /* Button Loading Animation */
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
    @php
        $pageTitle = $smsEnabled && !$whatsappEnabled ? 'Bulk SMS' : ($whatsappEnabled && !$smsEnabled ? 'Bulk WhatsApp' : 'Bulk Messaging');
    @endphp
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('notifications.index') }}">Communications</a>
        @endslot
        @slot('title')
            {{ $pageTitle }}
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-all me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="term-filter-wrapper">
        <select name="term" id="termId" class="form-select">
            @if (!empty($terms))
                @foreach ($terms as $term)
                    <option data-year="{{ $term->year }}"
                        value="{{ $term->id }}"{{ $term->id == session('selected_term_id', $currentTerm->id) ? 'selected' : '' }}>
                        {{ 'Term ' . $term->term . ', ' . $term->year }}</option>
                @endforeach
            @endif
        </select>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="admissions-container">
                <div class="admissions-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4>{{ $pageTitle }}</h4>
                            <p>
                                @if ($smsEnabled && $whatsappEnabled)
                                    Send and manage SMS and WhatsApp communications.
                                @elseif ($whatsappEnabled && !$smsEnabled)
                                    Send and manage template-driven WhatsApp staff communications.
                                @else
                                    Send and manage bulk SMS communications.
                                @endif
                            </p>
                        </div>
                        @php
                            $balanceBwp = $balance->balance_bwp ?? 0;
                            $costPerSms = \App\Helpers\SMSHelper::getPackageRate();
                            $unitsRemaining = $costPerSms > 0 ? floor($balanceBwp / $costPerSms) : 0;
                        @endphp
                        <div class="col-md-6">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h4 class="mb-0 fw-bold text-white" id="stat-total">{{ $messages->count() ?? 0 }}</h4>
                                        <small class="opacity-75">{{ $smsEnabled && !$whatsappEnabled ? 'Total SMS' : 'Total Messages' }}</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        @if ($smsEnabled)
                                            <h4 class="mb-0 fw-bold text-white" id="stat-balance">{{ number_format($balanceBwp, 2) }}</h4>
                                            <small class="opacity-75">Balance (BWP)</small>
                                        @else
                                            <h4 class="mb-0 fw-bold text-white">{{ $whatsappTemplates->count() }}</h4>
                                            <small class="opacity-75">WA Templates</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        @if ($smsEnabled)
                                            <h4 class="mb-0 fw-bold text-white" id="stat-units">{{ number_format($unitsRemaining) }}</h4>
                                            <small class="opacity-75">Units Left</small>
                                        @else
                                            <h4 class="mb-0 fw-bold text-white">Twilio</h4>
                                            <small class="opacity-75">WhatsApp Provider</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="admissions-body">
                    <div class="help-text">
                        <i class="fas fa-info-circle me-2"></i>
                        @if ($smsEnabled && $whatsappEnabled)
                            Send SMS to sponsors or staff, and send template-based WhatsApp broadcasts to eligible staff with recorded consent.
                        @elseif ($whatsappEnabled && !$smsEnabled)
                            Send template-based WhatsApp broadcasts to eligible staff with recorded consent.
                        @else
                            Send bulk SMS messages to sponsors (parents/guardians) or staff members. Monitor delivery status and track message costs.
                        @endif
                    </div>

    <!-- Background Job Indicator -->
    @if ($smsEnabled)
    <div class="background-job-indicator" id="background-job-indicator">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="mb-0">
                <i class="bx bx-loader bx-spin me-2"></i>
                SMS Sending in Background
            </h6>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="reopenProgressModal()">
                <i class="fas fa-expand"></i>
            </button>
        </div>
        <div class="progress" style="height: 20px;">
            <div id="bg-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                role="progressbar" style="width: 0%;">
                <span id="bg-progress-percentage">0%</span>
            </div>
        </div>
        <div class="mt-2 text-muted small">
            <span id="bg-progress-stats">Sent: 0 | Failed: 0</span>
        </div>
    </div>
    @endif

    <!-- Success/Error Toast Notifications -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="smsToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i id="toast-icon" class="fas fa-check-circle text-success me-2"></i>
                <strong class="me-auto" id="toast-title">Success</strong>
                <small id="toast-time">just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toast-message">
                SMS messages sent successfully!
            </div>
        </div>
    </div>

    <div class="row">
        <div style="margin-right:10px;" id="messages-term" class="col-12">
            <!-- Loading Placeholder - Initial State -->
            <!-- Communication Overview Cards Skeleton -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border" style="border-radius: 3px !important;">
                        <div class="card-body p-4">
                            <!-- Header Skeleton -->
                            <div class="d-flex align-items-center mb-4">
                                <div>
                                    <div class="skeleton skeleton-text skeleton-lg mb-2"
                                        style="height: 24px; width: 250px;"></div>
                                    <div class="skeleton skeleton-text skeleton-md" style="height: 14px; width: 300px;">
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <!-- Package Details Card Skeleton -->
                                <div class="col-md-4">
                                    <div class="position-relative bg-white shadow-sm border h-100"
                                        style="border-radius: 3px !important;">
                                        <div class="p-4">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-shrink-0">
                                                    <div class="skeleton skeleton-icon"></div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="skeleton skeleton-text skeleton-md mb-3"
                                                        style="height: 16px;"></div>
                                                    <div class="mb-2">
                                                        <div class="skeleton skeleton-text skeleton-sm"
                                                            style="height: 12px;"></div>
                                                    </div>
                                                    <div>
                                                        <div class="skeleton skeleton-text skeleton-sm"
                                                            style="height: 12px;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Balance & Usage Card Skeleton -->
                                <div class="col-md-4">
                                    <div class="position-relative bg-white shadow-sm border h-100"
                                        style="border-radius: 3px !important;">
                                        <div class="p-4">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-shrink-0">
                                                    <div class="skeleton skeleton-icon"></div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="skeleton skeleton-text skeleton-md mb-3"
                                                        style="height: 16px;"></div>
                                                    <div class="mb-2">
                                                        <div class="skeleton skeleton-text skeleton-sm"
                                                            style="height: 12px;"></div>
                                                    </div>
                                                    <div>
                                                        <div class="skeleton skeleton-text skeleton-sm"
                                                            style="height: 12px;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SMS Details Card Skeleton -->
                                <div class="col-md-4">
                                    <div class="position-relative bg-white shadow-sm border h-100"
                                        style="border-radius: 3px !important;">
                                        <div class="p-4">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-shrink-0">
                                                    <div class="skeleton skeleton-icon"></div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="skeleton skeleton-text skeleton-md mb-3"
                                                        style="height: 16px;"></div>
                                                    <div class="mb-2">
                                                        <div class="skeleton skeleton-text skeleton-sm"
                                                            style="height: 12px;"></div>
                                                    </div>
                                                    <div>
                                                        <div class="skeleton skeleton-badge"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages Table Skeleton -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>
                                <div class="skeleton skeleton-text skeleton-sm"></div>
                            </th>
                            <th>
                                <div class="skeleton skeleton-text skeleton-sm"></div>
                            </th>
                            <th>
                                <div class="skeleton skeleton-text skeleton-md"></div>
                            </th>
                            <th>
                                <div class="skeleton skeleton-text skeleton-sm"></div>
                            </th>
                            <th>
                                <div class="skeleton skeleton-text skeleton-sm"></div>
                            </th>
                            <th>
                                <div class="skeleton skeleton-text skeleton-sm"></div>
                            </th>
                            <th>
                                <div class="skeleton skeleton-text skeleton-sm"></div>
                            </th>
                            <th>
                                <div class="skeleton skeleton-text skeleton-sm"></div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 1; $i <= 5; $i++)
                            <tr>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-md"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-md"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-lg"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-badge"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-xs"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-xs"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-text skeleton-sm"></div>
                                </td>
                                <td>
                                    <div class="skeleton skeleton-badge"></div>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Bulk SMS Modal for Sponsors with Progress View -->
    <div class="modal fade" id="sendBulkSMSSponsorModal" tabindex="-1" aria-labelledby="sendBulkSMSSponsorModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Form View -->
                <div id="sponsor-form-view" class="modal-view active">
                    <div class="modal-header">
                        <h5 class="modal-title">Send Bulk SMS to Sponsors</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="bulkSmsForm">
                            <!-- Dynamic Recipient Count Display -->
                            <div class="alert alert-info py-2 mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>
                                        <i class="fas fa-users me-2"></i>
                                        <strong>Recipients:</strong>
                                        <span id="sponsor-recipient-count" class="badge bg-primary ms-2">
                                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                            Calculating...
                                        </span>
                                    </span>
                                    <span id="sponsor-cost-estimate" class="text-muted small"></span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="gradeSelect" class="form-label">Filter By Grade</label>
                                <select class="form-select form-select-sm" id="gradeSelect" name="grade">
                                    @if (!empty($grades))
                                        <option value="">Choose ...</option>
                                        @foreach ($grades as $grade)
                                            <option value="{{ $grade->id }}">{{ $grade->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="sponsorFilterSelect" class="form-label">Custom Filter</label>
                                <select class="form-select form-select-sm" id="sponsorFilterSelect" name="sponsorFilter">
                                    <option value="">Choose ...</option>
                                    @if (!empty($sponsor_filters))
                                        @foreach ($sponsor_filters as $filter)
                                            <option value="{{ $filter->id }}">{{ $filter->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message Template <small class="text-muted">(Optional)</small></label>
                                <select class="form-select form-select-sm" id="sponsorTemplateSelect" onchange="loadTemplate('sponsor')">
                                    <option value="">-- Select a template or type custom message --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message</label>
                                <textarea id="smsText" name="message" class="form-control form-control-sm" rows="4"
                                    placeholder="Type your message or select a template above..."></textarea>
                                <div class="d-flex justify-content-between mt-2">
                                    <div id="charCount">Characters: 0 | SMS Count: 0</div>
                                    <div id="sponsor-total-units" class="text-muted small"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col d-flex justify-content-end">
                                    <button type="button" class="btn btn-sm btn-primary btn-loading" id="sendBulkSMSButton"
                                        disabled>
                                        <span class="btn-text"><i class="fas fa-paper-plane me-1"></i> Send SMS</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Sending...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Progress View -->
                <div id="sponsor-progress-view" class="modal-view">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bx bx-loader bx-spin me-2"></i>
                            <span id="sponsor-progress-title">Sending SMS Messages...</span>
                        </h5>
                        <button type="button" class="btn-close" disabled></button>
                    </div>
                    <div class="modal-body">
                        <!-- Summary -->
                        <div class="alert alert-light border mb-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="text-muted small">Total</div>
                                    <div class="h5 mb-0" id="sponsor-total-recipients">0</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted small">Sent</div>
                                    <div class="h5 mb-0 text-success" id="sponsor-sent-count">0</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted small">Failed</div>
                                    <div class="h5 mb-0 text-danger" id="sponsor-failed-count">0</div>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="progress mb-3" style="height: 30px;">
                            <div id="sponsor-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                                role="progressbar" style="width: 0%;">
                                <span id="sponsor-progress-percentage">0%</span>
                            </div>
                        </div>

                        <!-- Status Message -->
                        <div class="text-center mb-3">
                            <span id="sponsor-progress-details" class="text-muted">Initializing...</span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="sendToBackground('sponsor')">
                                <i class="fas fa-minimize me-1"></i> Send to Background
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" id="sponsor-cancel-btn">
                                <i class="fas fa-stop-circle me-1"></i> Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Bulk SMS Modal for Staff with Progress View -->
    <div class="modal fade" id="sendBulkSMSUserModal" tabindex="-1" aria-labelledby="sendBulkSMSUserModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Form View -->
                <div id="user-form-view" class="modal-view active">
                    <div class="modal-header">
                        <h5 class="modal-title">Send Bulk SMS to Staff</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="bulkSmsUserForm">
                            <!-- Dynamic Recipient Count Display -->
                            <div class="alert alert-info py-2 mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>
                                        <i class="fas fa-users me-2"></i>
                                        <strong>Recipients:</strong>
                                        <span id="user-recipient-count" class="badge bg-primary ms-2">
                                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                            Calculating...
                                        </span>
                                    </span>
                                    <span id="user-cost-estimate" class="text-muted small"></span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="departmentSelect" class="form-label">Select Department</label>
                                <select class="form-select form-select-sm" id="departmentSelect" name="department">
                                    @if (!empty($departments))
                                        <option value="">Choose ...</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->name ?? '' }}">{{ $department->name ?? '' }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="areaOfWorkSelect" class="form-label">Select Area of Work</label>
                                <select class="form-select form-select-sm" id="areaOfWorkSelect" name="area_of_work">
                                    @if (!empty($area_of_work))
                                        <option value="">Choose ...</option>
                                        @foreach ($area_of_work as $area)
                                            <option value="{{ $area->name ?? '' }}">{{ $area->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="positionSelect" class="form-label">Select Position</label>
                                <select class="form-select form-select-sm" id="positionSelect" name="position">
                                    @if (!empty($positions))
                                        <option value="">Choose ...</option>
                                        @foreach ($positions as $position)
                                            <option value="{{ $position ?? '' }}">{{ $position ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="filterSelect" class="form-label">Select Filter</label>
                                <select class="form-select form-select-sm" id="filterSelect" name="filter">
                                    @if (!empty($user_filters))
                                        <option value="">Choose ...</option>
                                        @foreach ($user_filters as $filter)
                                            <option value="{{ $filter->id ?? '' }}">{{ $filter->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message Template <small class="text-muted">(Optional)</small></label>
                                <select class="form-select form-select-sm" id="userTemplateSelect" onchange="loadTemplate('user')">
                                    <option value="">-- Select a template or type custom message --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message</label>
                                <textarea id="smsTextUser" name="message" class="form-control form-control-sm" rows="4"
                                    placeholder="Type your message or select a template above..."></textarea>
                                <div class="d-flex justify-content-between mt-2">
                                    <div id="charCountUser">Characters: 0 | SMS Count: 0</div>
                                    <div id="user-total-units" class="text-muted small"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col d-flex justify-content-end">
                                    <button type="button" class="btn btn-sm btn-primary btn-loading" id="sendBulkSMSUserButton"
                                        disabled>
                                        <span class="btn-text"><i class="fas fa-paper-plane me-1"></i> Send SMS</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Sending...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Progress View -->
                <div id="user-progress-view" class="modal-view">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bx bx-loader bx-spin me-2"></i>
                            <span id="user-progress-title">Sending SMS Messages...</span>
                        </h5>
                        <button type="button" class="btn-close" disabled></button>
                    </div>
                    <div class="modal-body">
                        <!-- Summary -->
                        <div class="alert alert-light border mb-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="text-muted small">Total</div>
                                    <div class="h5 mb-0" id="user-total-recipients">0</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted small">Sent</div>
                                    <div class="h5 mb-0 text-success" id="user-sent-count">0</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted small">Failed</div>
                                    <div class="h5 mb-0 text-danger" id="user-failed-count">0</div>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="progress mb-3" style="height: 30px;">
                            <div id="user-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                                role="progressbar" style="width: 0%;">
                                <span id="user-progress-percentage">0%</span>
                            </div>
                        </div>

                        <!-- Status Message -->
                        <div class="text-center mb-3">
                            <span id="user-progress-details" class="text-muted">Initializing...</span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="sendToBackground('user')">
                                <i class="fas fa-minimize me-1"></i> Send to Background
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" id="user-cancel-btn">
                                <i class="fas fa-stop-circle me-1"></i> Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="sendBulkWhatsAppUserModal" tabindex="-1" aria-labelledby="sendBulkWhatsAppUserModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendBulkWhatsAppUserModalLabel">
                        <i class="fab fa-whatsapp me-2 text-success"></i>Send Bulk WhatsApp to Staff
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-users me-2"></i>
                                <strong>Eligible staff:</strong>
                                <span id="wa-user-recipient-count" class="badge bg-success ms-2">0</span>
                            </span>
                            <span class="text-muted small">Skipped: <span id="wa-user-skipped-count">0</span></span>
                        </div>
                    </div>
                    <form id="bulkWhatsappUserForm">
                        <div class="mb-3">
                            <label for="waDepartmentSelect" class="form-label">Select Department</label>
                            <select class="form-select form-select-sm" id="waDepartmentSelect" name="department">
                                <option value="">Choose ...</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->name ?? '' }}">{{ $department->name ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="waAreaOfWorkSelect" class="form-label">Select Area of Work</label>
                            <select class="form-select form-select-sm" id="waAreaOfWorkSelect" name="area_of_work">
                                <option value="">Choose ...</option>
                                @foreach ($area_of_work as $area)
                                    <option value="{{ $area->name ?? '' }}">{{ $area->name ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="waPositionSelect" class="form-label">Select Position</label>
                            <select class="form-select form-select-sm" id="waPositionSelect" name="position">
                                <option value="">Choose ...</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position ?? '' }}">{{ $position ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="waFilterSelect" class="form-label">Select Filter</label>
                            <select class="form-select form-select-sm" id="waFilterSelect" name="filter">
                                <option value="">Choose ...</option>
                                @foreach ($user_filters as $filter)
                                    <option value="{{ $filter->id ?? '' }}">{{ $filter->name ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="waTemplateSelect" class="form-label">WhatsApp Template</label>
                            <select class="form-select form-select-sm" id="waTemplateSelect" name="template_id">
                                <option value="">Select a template...</option>
                                @foreach ($whatsappTemplates as $template)
                                    <option
                                        value="{{ $template->id }}"
                                        data-preview="{{ e($template->body_preview ?? '') }}"
                                        data-variables="{{ e(json_encode($template->variables ?? [])) }}">
                                        {{ $template->name }} ({{ strtoupper($template->language) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Template Preview</label>
                            <div id="waTemplatePreview" class="form-control bg-light" style="min-height: 90px;">Select a template to preview its content.</div>
                        </div>
                        <div id="waTemplateVariables"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-success btn-loading" id="sendBulkWhatsAppUserButton" disabled>
                        <span class="btn-text"><i class="fab fa-whatsapp me-1"></i> Send WhatsApp</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Sending...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        let currentJobId = null;
        let progressInterval = null;
        let currentModalType = null; // 'sponsor' or 'user'
        // SMS cost per unit is now dynamically fetched from the system (account_balances table)
        // No more hardcoded values from ENV file

        // Cache for recipient counts to reduce API calls
        let recipientCache = {
            sponsors: {},
            users: {}
        };

        // ============================================
        // Modal View Management
        // ============================================
        function switchModalView(modalType, viewType) {
            const formView = document.getElementById(`${modalType}-form-view`);
            const progressView = document.getElementById(`${modalType}-progress-view`);

            if (viewType === 'form') {
                formView.classList.add('active');
                progressView.classList.remove('active');
            } else {
                formView.classList.remove('active');
                progressView.classList.add('active');
            }
        }

        // ============================================
        // Background Job Management
        // ============================================
        function sendToBackground(modalType) {
            // Hide the modal
            if (modalType === 'sponsor') {
                $('#sendBulkSMSSponsorModal').modal('hide');
            } else {
                $('#sendBulkSMSUserModal').modal('hide');
            }

            // Show background indicator
            document.getElementById('background-job-indicator').classList.add('show');

            // Continue tracking in background
            currentModalType = null; // Mark as background job
        }

        function reopenProgressModal() {
            // Determine which modal to reopen based on current job
            if (currentJobId) {
                // You might want to store which type of job is running
                // For now, we'll try to determine based on the last used modal
                if (document.getElementById('sponsor-progress-view').classList.contains('active')) {
                    currentModalType = 'sponsor';
                    $('#sendBulkSMSSponsorModal').modal('show');
                } else if (document.getElementById('user-progress-view').classList.contains('active')) {
                    currentModalType = 'user';
                    $('#sendBulkSMSUserModal').modal('show');
                }
            }

            // Hide background indicator
            document.getElementById('background-job-indicator').classList.remove('show');
        }

        // ============================================
        // Term Selection and Data Loading
        // ============================================
        $(document).ready(function() {
            $('#termId').change(function() {
                var term = $(this).val();
                var studentsSessionUrl = "{{ route('students.term-session') }}";

                // Show skeleton loader when term changes
                showSkeletonLoader();

                $.ajax({
                    url: studentsSessionUrl,
                    method: 'POST',
                    data: {
                        term_id: term,
                        _token: '{{ csrf_token() }}'
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", xhr.status, xhr.statusText);
                    },
                    success: function() {
                        fetchTermMessages();
                    }
                });
            });

            function showSkeletonLoader() {
                // Skeleton loader HTML (same as before)
                var skeletonHTML = `<!-- Skeleton HTML here -->`;
                $('#messages-term').html(skeletonHTML);
            }

            function fetchTermMessages() {
                var messagesByTermUrl = "{{ route('notifications.get-messages') }}";
                $.ajax({
                    url: messagesByTermUrl,
                    method: 'GET',
                    success: function(response) {
                        $('#messages-term').html(response);
                        // Don't initialize DataTable - the loaded view (messages-term.blade.php)
                        // has its own custom filtering and pagination system
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching term data:", error);
                    }
                });
            }

            $('#termId').trigger('change');
        });

        // ============================================
        // Dynamic Recipient Count for SMS Modals
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {

            // ============================================
            // Load SMS Templates
            // ============================================
            let smsTemplates = [];

            function loadSmsTemplates() {
                fetch('{{ route("sms-templates.api.list") }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.templates) {
                            smsTemplates = data.templates;
                            populateTemplateDropdowns(data.templates, data.categories);
                        }
                    })
                    .catch(error => {
                        console.error('Failed to load SMS templates:', error);
                    });
            }

            function populateTemplateDropdowns(templates, categories) {
                const sponsorSelect = document.getElementById('sponsorTemplateSelect');
                const userSelect = document.getElementById('userTemplateSelect');

                // Group templates by category
                const grouped = {};
                templates.forEach(template => {
                    if (!grouped[template.category]) {
                        grouped[template.category] = [];
                    }
                    grouped[template.category].push(template);
                });

                // Build options HTML
                let optionsHtml = '<option value="">-- Select a template or type custom message --</option>';
                Object.keys(grouped).forEach(category => {
                    const categoryLabel = categories[category] || category;
                    optionsHtml += `<optgroup label="${categoryLabel}">`;
                    grouped[category].forEach(template => {
                        optionsHtml += `<option value="${template.id}" data-content="${encodeURIComponent(template.content)}">${template.name} (${template.sms_units} SMS)</option>`;
                    });
                    optionsHtml += '</optgroup>';
                });

                if (sponsorSelect) sponsorSelect.innerHTML = optionsHtml;
                if (userSelect) userSelect.innerHTML = optionsHtml;
            }

            // Load templates on page load
            loadSmsTemplates();

            // Make loadTemplate function globally accessible
            window.loadTemplate = function(type) {
                const selectId = type === 'sponsor' ? 'sponsorTemplateSelect' : 'userTemplateSelect';
                const textareaId = type === 'sponsor' ? 'smsText' : 'smsTextUser';
                const charCountId = type === 'sponsor' ? 'charCount' : 'charCountUser';

                const select = document.getElementById(selectId);
                const textarea = document.getElementById(textareaId);
                const selectedOption = select.options[select.selectedIndex];

                if (selectedOption && selectedOption.value) {
                    const content = decodeURIComponent(selectedOption.dataset.content || '');
                    textarea.value = content;

                    // Update character count
                    const length = content.length;
                    const smsCount = Math.ceil(length / 160) || 0;
                    document.getElementById(charCountId).textContent = `Characters: ${length} | SMS Count: ${smsCount}`;

                    // Trigger input event to update cost estimates and button state
                    textarea.dispatchEvent(new Event('input'));
                }
            };

            // ============================================
            // Sponsor Modal Dynamic Count
            // ============================================
            let sponsorDebounceTimer;
            const sponsorFilters = ['gradeSelect', 'sponsorFilterSelect'];

            sponsorFilters.forEach(filterId => {
                const element = document.getElementById(filterId);
                if (element) {
                    element.addEventListener('change', () => {
                        clearTimeout(sponsorDebounceTimer);
                        sponsorDebounceTimer = setTimeout(() => updateSponsorRecipientCount(), 300);
                    });
                }
            });

            const smsTextSponsor = document.getElementById('smsText');
            smsTextSponsor.addEventListener('input', function() {
                const text = smsTextSponsor.value;
                const length = text.length;
                const smsCount = Math.ceil(length / 160) || 0;
                document.getElementById('charCount').textContent =
                    `Characters: ${length} | SMS Count: ${smsCount}`;

                const recipientBadge = document.getElementById('sponsor-recipient-count');
                const currentCount = parseInt(recipientBadge.dataset.count) || 0;
                if (currentCount > 0) {
                    const costPerUnit = recipientCache.sponsorCostPerUnit || 0.35; // Fallback to default
                    updateSponsorCostEstimate(currentCount, smsCount, costPerUnit);
                }

                const sendButton = document.getElementById('sendBulkSMSButton');
                sendButton.disabled = !(currentCount > 0 && text.trim() !== '');
            });

            function updateSponsorRecipientCount() {
                const grade = document.getElementById('gradeSelect').value;
                const sponsorFilter = document.getElementById('sponsorFilterSelect').value;
                const cacheKey = `${grade}_${sponsorFilter}`;

                if (recipientCache.sponsors[cacheKey] !== undefined) {
                    const costPerUnit = recipientCache.sponsorCostPerUnit || 0.35; // Fallback to default
                    displaySponsorCount(recipientCache.sponsors[cacheKey], costPerUnit);
                    return;
                }

                const recipientBadge = document.getElementById('sponsor-recipient-count');
                recipientBadge.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Calculating...';
                recipientBadge.className = 'badge bg-secondary ms-2';
                document.getElementById('sendBulkSMSButton').disabled = true;

                fetch("{{ route('notifications.check-sms-recipients') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            grade: grade,
                            sponsorFilter: sponsorFilter,
                            recipientType: 'sponsors'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            recipientCache.sponsors[cacheKey] = data.count;
                            // Store the dynamic cost per unit from the system
                            recipientCache.sponsorCostPerUnit = data.costPerUnit;
                            displaySponsorCount(data.count, data.costPerUnit);
                        } else {
                            displaySponsorCount(0);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching recipient count:', error);
                        recipientBadge.innerHTML = 'Error';
                        recipientBadge.className = 'badge bg-danger ms-2';
                    });
            }

            function displaySponsorCount(count, costPerUnit) {
                const recipientBadge = document.getElementById('sponsor-recipient-count');
                const sendButton = document.getElementById('sendBulkSMSButton');
                const message = document.getElementById('smsText').value;

                recipientBadge.textContent = count.toLocaleString();
                recipientBadge.dataset.count = count; // Store numeric value for calculations

                if (count === 0) {
                    recipientBadge.className = 'badge bg-warning ms-2';
                    sendButton.disabled = true;
                    document.getElementById('sponsor-cost-estimate').textContent = 'No recipients found';
                    document.getElementById('sponsor-total-units').textContent = '';
                } else {
                    recipientBadge.className = 'badge bg-success ms-2';
                    sendButton.disabled = message.trim() === '';
                    const smsCount = Math.ceil(message.length / 160) || 1;
                    updateSponsorCostEstimate(count, smsCount, costPerUnit);
                }
            }

            function updateSponsorCostEstimate(recipientCount, smsCount, costPerUnit) {
                const totalUnits = recipientCount * smsCount;
                const totalCost = totalUnits * costPerUnit;

                document.getElementById('sponsor-cost-estimate').innerHTML =
                    `Est. Cost: <strong>BWP ${totalCost.toFixed(2)}</strong> (${costPerUnit} BWP per unit)`;
                document.getElementById('sponsor-total-units').textContent =
                    `Total Units: ${totalUnits.toLocaleString()}`;
            }

            // ============================================
            // User Modal Dynamic Count (similar setup)
            // ============================================
            let userDebounceTimer;
            const userFilters = ['departmentSelect', 'areaOfWorkSelect', 'positionSelect', 'filterSelect'];
            userFilters.forEach(filterId => {
                const element = document.getElementById(filterId);
                if (element) {
                    element.addEventListener('change', () => {
                        clearTimeout(userDebounceTimer);
                        userDebounceTimer = setTimeout(() => updateUserRecipientCount(), 300);
                    });
                }
            });

            const smsTextUser = document.getElementById('smsTextUser');
            smsTextUser.addEventListener('input', function() {
                const text = smsTextUser.value;
                const length = text.length;
                const smsCount = Math.ceil(length / 160) || 0;
                document.getElementById('charCountUser').textContent =
                    `Characters: ${length} | SMS Count: ${smsCount}`;

                const recipientBadge = document.getElementById('user-recipient-count');
                const currentCount = parseInt(recipientBadge.dataset.count) || 0;
                if (currentCount > 0) {
                    const costPerUnit = recipientCache.userCostPerUnit || 0.35; // Fallback to default
                    updateUserCostEstimate(currentCount, smsCount, costPerUnit);
                }

                const sendButton = document.getElementById('sendBulkSMSUserButton');
                sendButton.disabled = !(currentCount > 0 && text.trim() !== '');
            });

            function updateUserRecipientCount() {
                const department = document.getElementById('departmentSelect').value;
                const areaOfWork = document.getElementById('areaOfWorkSelect').value;
                const position = document.getElementById('positionSelect').value;
                const filter = document.getElementById('filterSelect').value;
                const cacheKey = `${department}_${areaOfWork}_${position}_${filter}`;

                if (recipientCache.users[cacheKey] !== undefined) {
                    const costPerUnit = recipientCache.userCostPerUnit || 0.35; // Fallback to default
                    displayUserCount(recipientCache.users[cacheKey], costPerUnit);
                    return;
                }

                const recipientBadge = document.getElementById('user-recipient-count');
                recipientBadge.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Calculating...';
                recipientBadge.className = 'badge bg-secondary ms-2';
                document.getElementById('sendBulkSMSUserButton').disabled = true;

                fetch("{{ route('notifications.check-sms-recipients') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            department: department,
                            areaOfWork: areaOfWork,
                            position: position,
                            filter: filter,
                            recipientType: 'users'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            recipientCache.users[cacheKey] = data.count;
                            // Store the dynamic cost per unit from the system
                            recipientCache.userCostPerUnit = data.costPerUnit;
                            displayUserCount(data.count, data.costPerUnit);
                        } else {
                            displayUserCount(0);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching recipient count:', error);
                        recipientBadge.innerHTML = 'Error';
                        recipientBadge.className = 'badge bg-danger ms-2';
                    });
            }

            function displayUserCount(count, costPerUnit) {
                const recipientBadge = document.getElementById('user-recipient-count');
                const sendButton = document.getElementById('sendBulkSMSUserButton');
                const message = document.getElementById('smsTextUser').value;

                recipientBadge.textContent = count.toLocaleString();
                recipientBadge.dataset.count = count; // Store numeric value for calculations

                if (count === 0) {
                    recipientBadge.className = 'badge bg-warning ms-2';
                    sendButton.disabled = true;
                    document.getElementById('user-cost-estimate').textContent = 'No recipients found';
                    document.getElementById('user-total-units').textContent = '';
                } else {
                    recipientBadge.className = 'badge bg-success ms-2';
                    sendButton.disabled = message.trim() === '';
                    const smsCount = Math.ceil(message.length / 160) || 1;
                    updateUserCostEstimate(count, smsCount, costPerUnit);
                }
            }

            function updateUserCostEstimate(recipientCount, smsCount, costPerUnit) {
                const totalUnits = recipientCount * smsCount;
                const totalCost = totalUnits * costPerUnit;

                document.getElementById('user-cost-estimate').innerHTML =
                    `Est. Cost: <strong>BWP ${totalCost.toFixed(2)}</strong> (${costPerUnit} BWP per unit)`;
                document.getElementById('user-total-units').textContent =
                    `Total Units: ${totalUnits.toLocaleString()}`;
            }

            // ============================================
            // Modal Event Handlers
            // ============================================
            $('#sendBulkSMSSponsorModal').on('shown.bs.modal', function() {
                if (document.getElementById('sponsor-form-view').classList.contains('active')) {
                    updateSponsorRecipientCount();
                }
            });

            $('#sendBulkSMSUserModal').on('shown.bs.modal', function() {
                if (document.getElementById('user-form-view').classList.contains('active')) {
                    updateUserRecipientCount();
                }
            });

            $('#sendBulkSMSSponsorModal').on('hidden.bs.modal', function() {
                if (!currentJobId) {
                    switchModalView('sponsor', 'form');
                    document.getElementById('bulkSmsForm').reset();
                    document.getElementById('charCount').textContent = 'Characters: 0 | SMS Count: 0';
                    document.getElementById('sponsor-total-units').textContent = '';
                }
            });

            $('#sendBulkSMSUserModal').on('hidden.bs.modal', function() {
                if (!currentJobId) {
                    switchModalView('user', 'form');
                    document.getElementById('bulkSmsUserForm').reset();
                    document.getElementById('charCountUser').textContent = 'Characters: 0 | SMS Count: 0';
                    document.getElementById('user-total-units').textContent = '';
                }
            });

            // ============================================
            // Send Button Handlers
            // ============================================
            document.getElementById('sendBulkSMSButton').addEventListener('click', function() {
                const message = document.getElementById('smsText').value;
                const grade = document.getElementById('gradeSelect').value;
                const sponsorFilter = document.getElementById('sponsorFilterSelect').value;
                const recipientCount = parseInt(document.getElementById('sponsor-recipient-count')
                    .dataset.count) || 0;

                if (message.trim() === '' || recipientCount === 0) return;

                const confirmSend = confirm(
                    `You are about to send SMS to ${recipientCount} recipients. Do you want to proceed?`
                );

                if (confirmSend) {
                    switchModalView('sponsor', 'progress');
                    currentModalType = 'sponsor';
                    sendBulkSMSWithProgress(message, 'sponsors', {
                        grade: grade,
                        sponsorFilter: sponsorFilter
                    }, recipientCount);
                }
            });

            document.getElementById('sendBulkSMSUserButton').addEventListener('click', function() {
                const message = document.getElementById('smsTextUser').value;
                const department = document.getElementById('departmentSelect').value;
                const areaOfWork = document.getElementById('areaOfWorkSelect').value;
                const position = document.getElementById('positionSelect').value;
                const filter = document.getElementById('filterSelect').value;
                const recipientCount = parseInt(document.getElementById('user-recipient-count')
                    .dataset.count) || 0;

                if (message.trim() === '' || recipientCount === 0) return;

                const confirmSend = confirm(
                    `You are about to send SMS to ${recipientCount} recipients. Do you want to proceed?`
                );

                if (confirmSend) {
                    switchModalView('user', 'progress');
                    currentModalType = 'user';
                    sendBulkSMSWithProgress(message, 'users', {
                        department: department,
                        area_of_work: areaOfWork,
                        position: position,
                        filter: filter
                    }, recipientCount);
                }
            });

            // ============================================
            // Cancel Button Handlers
            // ============================================
            document.getElementById('sponsor-cancel-btn').addEventListener('click', function() {
                if (!currentJobId) return;
                if (confirm(
                        'Are you sure you want to cancel this SMS job? Messages already sent cannot be recalled.'
                    )) {
                    cancelJob();
                }
            });

            document.getElementById('user-cancel-btn').addEventListener('click', function() {
                if (!currentJobId) return;
                if (confirm(
                        'Are you sure you want to cancel this SMS job? Messages already sent cannot be recalled.'
                    )) {
                    cancelJob();
                }
            });
        });

        // ============================================
        // SMS Sending with Progress Tracking
        // ============================================
        function sendBulkSMSWithProgress(message, recipientType, filters, expectedCount) {
            const requestData = {
                message: message,
                recipientType: recipientType,
                ...filters
            };

            // Set total recipients in the modal
            if (currentModalType === 'sponsor') {
                document.getElementById('sponsor-total-recipients').textContent = expectedCount;
            } else {
                document.getElementById('user-total-recipients').textContent = expectedCount;
            }

            fetch("{{ route('notifications.send-bulk-sms') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => {
                    // Handle validation errors (422) and other error responses
                    if (!response.ok) {
                        return response.json().then(data => {
                            // Extract error message from validation errors or general message
                            let errorMessage = data.message || 'Failed to initiate SMS sending';
                            if (data.errors) {
                                // Get first error message from validation errors
                                const firstError = Object.values(data.errors)[0];
                                errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                            }
                            throw new Error(errorMessage);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.jobId) {
                        currentJobId = data.jobId;
                        startProgressTracking(data.jobId, expectedCount);
                    } else {
                        showToast('error', 'Send Failed', data.message || 'Failed to initiate SMS sending');
                        switchModalView(currentModalType, 'form');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'Send Failed', error.message || 'Failed to send SMS. Please try again.');
                    switchModalView(currentModalType, 'form');
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const waFilters = ['waDepartmentSelect', 'waAreaOfWorkSelect', 'waPositionSelect', 'waFilterSelect'];
            const waTemplateSelect = document.getElementById('waTemplateSelect');
            const waTemplatePreview = document.getElementById('waTemplatePreview');
            const waTemplateVariables = document.getElementById('waTemplateVariables');
            const waSendButton = document.getElementById('sendBulkWhatsAppUserButton');

            if (!waTemplateSelect || !waSendButton) {
                return;
            }

            function refreshWhatsAppRecipientCount() {
                const requestData = {
                    department: document.getElementById('waDepartmentSelect').value,
                    area_of_work: document.getElementById('waAreaOfWorkSelect').value,
                    position: document.getElementById('waPositionSelect').value,
                    filter: document.getElementById('waFilterSelect').value
                };

                fetch('{{ route("notifications.check-whatsapp-recipients") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('wa-user-recipient-count').textContent = data.eligible ?? data.count ?? 0;
                    document.getElementById('wa-user-skipped-count').textContent = data.skipped ?? 0;
                    updateWhatsAppSendButton();
                })
                .catch(() => {
                    document.getElementById('wa-user-recipient-count').textContent = '0';
                    document.getElementById('wa-user-skipped-count').textContent = '0';
                    updateWhatsAppSendButton();
                });
            }

            function renderWhatsAppTemplateVariables() {
                const option = waTemplateSelect.options[waTemplateSelect.selectedIndex];
                const preview = option?.dataset.preview || 'Select a template to preview its content.';
                const variables = option?.dataset.variables ? JSON.parse(option.dataset.variables) : {};

                waTemplatePreview.textContent = preview || 'Select a template to preview its content.';
                waTemplateVariables.innerHTML = '';

                Object.keys(variables || {}).forEach((key) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'mb-3';
                    wrapper.innerHTML = `
                        <label class="form-label" for="wa-template-variable-${key}">${key}</label>
                        <input type="text" class="form-control wa-template-variable" id="wa-template-variable-${key}" data-variable-key="${key}" placeholder="Enter ${key}">
                    `;
                    waTemplateVariables.appendChild(wrapper);
                });

                updateWhatsAppSendButton();
            }

            function updateWhatsAppSendButton() {
                const eligibleCount = parseInt(document.getElementById('wa-user-recipient-count').textContent || '0', 10);
                waSendButton.disabled = !(eligibleCount > 0 && waTemplateSelect.value);
            }

            waFilters.forEach((filterId) => {
                const element = document.getElementById(filterId);
                if (element) {
                    element.addEventListener('change', refreshWhatsAppRecipientCount);
                }
            });

            waTemplateSelect.addEventListener('change', renderWhatsAppTemplateVariables);

            $('#sendBulkWhatsAppUserModal').on('shown.bs.modal', function() {
                refreshWhatsAppRecipientCount();
            });

            $('#sendBulkWhatsAppUserModal').on('hidden.bs.modal', function() {
                document.getElementById('bulkWhatsappUserForm').reset();
                waTemplatePreview.textContent = 'Select a template to preview its content.';
                waTemplateVariables.innerHTML = '';
                document.getElementById('wa-user-recipient-count').textContent = '0';
                document.getElementById('wa-user-skipped-count').textContent = '0';
                waSendButton.classList.remove('loading');
                waSendButton.disabled = true;
            });

            waSendButton.addEventListener('click', function() {
                const eligibleCount = parseInt(document.getElementById('wa-user-recipient-count').textContent || '0', 10);
                if (eligibleCount <= 0 || !waTemplateSelect.value) {
                    return;
                }

                if (!confirm(`You are about to send WhatsApp to ${eligibleCount} eligible staff recipients. Do you want to proceed?`)) {
                    return;
                }

                const templateVariables = {};
                document.querySelectorAll('.wa-template-variable').forEach((field) => {
                    templateVariables[field.dataset.variableKey] = field.value;
                });

                waSendButton.classList.add('loading');
                waSendButton.disabled = true;

                fetch('{{ route("notifications.send-bulk-message") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        channel: 'whatsapp',
                        recipientType: 'users',
                        department: document.getElementById('waDepartmentSelect').value,
                        area_of_work: document.getElementById('waAreaOfWorkSelect').value,
                        position: document.getElementById('waPositionSelect').value,
                        filter: document.getElementById('waFilterSelect').value,
                        template_id: waTemplateSelect.value,
                        template_variables: templateVariables
                    })
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Failed to send WhatsApp broadcast.');
                    }
                    return data;
                })
                .then(data => {
                    waSendButton.classList.remove('loading');
                    waSendButton.disabled = false;
                    $('#sendBulkWhatsAppUserModal').modal('hide');
                    const summary = data.summary || {};
                    alert(`WhatsApp broadcast processed. Sent: ${summary.sent || 0}, Failed: ${summary.failed || 0}, Skipped: ${summary.skipped || 0}`);
                    window.location.reload();
                })
                .catch(error => {
                    waSendButton.classList.remove('loading');
                    waSendButton.disabled = false;
                    alert(error.message || 'Failed to send WhatsApp broadcast.');
                });
            });
        });

        function startProgressTracking(jobId, totalRecipients) {
            if (progressInterval) {
                clearInterval(progressInterval);
            }

            progressInterval = setInterval(() => {
                checkJobProgress(jobId);
            }, 2000);

            checkJobProgress(jobId);
        }

        function checkJobProgress(jobId) {
            fetch(`{{ route('notifications.job-progress', ':jobId') }}`.replace(':jobId', jobId), {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.progress) {
                        updateProgressUI(data.progress);

                        if (['completed', 'failed', 'cancelled', 'not_found'].includes(data.progress.status)) {
                            stopProgressTracking();
                            handleJobCompletion(data.progress);
                        }
                    }
                })
                .catch(error => {
                    console.error('Progress check error:', error);
                });
        }

        function updateProgressUI(progress) {
            // Update both modal and background indicator
            if (currentModalType === 'sponsor') {
                updateModalProgress('sponsor', progress);
            } else if (currentModalType === 'user') {
                updateModalProgress('user', progress);
            }

            // Also update background indicator if it's visible
            if (document.getElementById('background-job-indicator').classList.contains('show')) {
                updateBackgroundProgress(progress);
            }
        }

        function updateModalProgress(modalType, progress) {
            const progressBar = document.getElementById(`${modalType}-progress-bar`);
            const progressPercentage = document.getElementById(`${modalType}-progress-percentage`);
            const progressDetails = document.getElementById(`${modalType}-progress-details`);
            const sentCount = document.getElementById(`${modalType}-sent-count`);
            const failedCount = document.getElementById(`${modalType}-failed-count`);

            progressBar.style.width = `${progress.percentage}%`;
            progressPercentage.textContent = `${progress.percentage}%`;
            sentCount.textContent = progress.sent || 0;
            failedCount.textContent = progress.failed || 0;
            progressDetails.textContent = progress.message || 'Processing...';

            progressBar.className = 'progress-bar progress-bar-striped';
            if (progress.status === 'completed') {
                progressBar.classList.add('bg-success');
            } else if (progress.status === 'failed' || progress.status === 'cancelled') {
                progressBar.classList.add('bg-danger');
            } else {
                progressBar.classList.add('progress-bar-animated', 'bg-info');
            }
        }

        function updateBackgroundProgress(progress) {
            const bgProgressBar = document.getElementById('bg-progress-bar');
            const bgPercentage = document.getElementById('bg-progress-percentage');
            const bgStats = document.getElementById('bg-progress-stats');

            bgProgressBar.style.width = `${progress.percentage}%`;
            bgPercentage.textContent = `${progress.percentage}%`;
            bgStats.textContent = `Sent: ${progress.sent || 0} | Failed: ${progress.failed || 0}`;

            bgProgressBar.className = 'progress-bar progress-bar-striped';
            if (progress.status === 'completed') {
                bgProgressBar.classList.add('bg-success');
            } else if (progress.status === 'failed' || progress.status === 'cancelled') {
                bgProgressBar.classList.add('bg-danger');
            } else {
                bgProgressBar.classList.add('progress-bar-animated', 'bg-info');
            }
        }

        function handleJobCompletion(progress) {
            const modalType = currentModalType || (document.getElementById('sponsor-progress-view').classList.contains(
                'active') ? 'sponsor' : 'user');
            const progressTitle = document.getElementById(`${modalType}-progress-title`);

            if (progress.status === 'completed') {
                progressTitle.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>SMS Sending Completed';
                showToast('success', 'Success',
                    `Successfully sent ${progress.sent} messages${progress.failed > 0 ? `, ${progress.failed} failed` : ''}`
                );
            } else if (progress.status === 'failed') {
                progressTitle.innerHTML = '<i class="fas fa-times-circle text-danger me-2"></i>SMS Sending Failed';
                showToast('error', 'Failed', progress.message || 'SMS sending failed');
            } else if (progress.status === 'cancelled') {
                progressTitle.innerHTML = '<i class="fas fa-ban text-warning me-2"></i>SMS Sending Cancelled';
                showToast('warning', 'Cancelled', 'SMS sending was cancelled');
            }

            // Clean up
            currentJobId = null;
            currentModalType = null;

            // Hide background indicator if shown
            document.getElementById('background-job-indicator').classList.remove('show');

            // Reset modal after delay
            setTimeout(() => {
                if (modalType === 'sponsor') {
                    $('#sendBulkSMSSponsorModal').modal('hide');
                } else {
                    $('#sendBulkSMSUserModal').modal('hide');
                }
                $('#termId').trigger('change');
            }, 3000);
        }

        function cancelJob() {
            if (!currentJobId) return;

            fetch(`{{ route('notifications.job-cancel', ':jobId') }}`.replace(':jobId', currentJobId), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('warning', 'Cancelled', 'SMS job cancellation requested');
                    }
                })
                .catch(error => {
                    console.error('Cancel error:', error);
                    showToast('error', 'Error', 'Failed to cancel job');
                });
        }

        function stopProgressTracking() {
            if (progressInterval) {
                clearInterval(progressInterval);
                progressInterval = null;
            }
        }

        function showToast(type, title, message) {
            const toast = document.getElementById('smsToast');
            const toastIcon = document.getElementById('toast-icon');
            const toastTitle = document.getElementById('toast-title');
            const toastMessage = document.getElementById('toast-message');

            toastIcon.className = type === 'success' ? 'fas fa-check-circle text-success me-2' :
                type === 'error' ? 'fas fa-times-circle text-danger me-2' :
                'fas fa-exclamation-triangle text-warning me-2';

            toastTitle.textContent = title;
            toastMessage.textContent = message;

            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }

        window.addEventListener('beforeunload', function() {
            stopProgressTracking();
        });
    </script>
@endsection

@extends('layouts.master')
@section('title')
    Communications Module
@endsection
@section('css')
    <link rel="stylesheet" type="text/css"
        href="https://cdn.jsdelivr.net/npm/tooltipster/dist/css/tooltipster.bundle.min.css" />
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

        .admissions-body {
            padding: 24px;
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

        .dataTables_length select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1em;
            padding-right: 2.5rem;
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
        }

        .skeleton-text.skeleton-sm {
            width: 50%;
        }

        .skeleton-text.skeleton-md {
            width: 70%;
        }

        .skeleton-text.skeleton-lg {
            width: 85%;
        }

        .skeleton-badge {
            width: 60px;
            height: 20px;
            display: inline-block;
            background-color: #e9ecef;
            border-radius: 3px;
        }

        .skeleton-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background-color: #e9ecef;
        }

        .skeleton-button {
            width: 110px;
            height: 28px;
            display: inline-block;
            background-color: #e9ecef;
            border-radius: 3px;
        }

        .skeleton-link {
            width: 40px;
            height: 14px;
            display: inline-block;
            background-color: #e9ecef;
            border-radius: 2px;
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

        /* Custom File Input */
        .custom-file-input {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .custom-file-input input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #fff;
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-input-label:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
        }

        .file-input-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .file-input-text {
            flex: 1;
        }

        .file-input-text .file-label {
            display: block;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .file-input-text .file-hint {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        .file-input-text .file-name {
            display: block;
            font-size: 13px;
            color: #059669;
            margin-top: 4px;
            font-weight: 500;
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
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('notifications.index') }}">Communications</a>
        @endslot
        @slot('title')
            Bulk Mailing
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
                            <h4>Bulk Emails</h4>
                            <p>Send and manage bulk email communications</p>
                        </div>
                        <div class="col-md-6">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h4 class="mb-0 fw-bold text-white" id="stat-total">{{ $emails->count() ?? 0 }}</h4>
                                        <small class="opacity-75">Total Emails</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h4 class="mb-0 fw-bold text-white" id="stat-bulk">--</h4>
                                        <small class="opacity-75">Bulk</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h4 class="mb-0 fw-bold text-white" id="stat-individual">--</h4>
                                        <small class="opacity-75">Individual</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="admissions-body">
                    <div class="help-text">
                        <i class="fas fa-info-circle me-2"></i>
                        Send bulk emails to sponsors (parents/guardians) or staff members. Filter recipients by grade, department, or custom filters.
                    </div>

                    <div id="emails-term">
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

                                <!-- Email Details Card Skeleton -->
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
    </div>

    <!-- Enhanced Bulk Email Modal for Sponsors with Dynamic Recipient Count -->
    <div class="modal fade" id="sendBulkEmailSponsorModal" tabindex="-1"
        aria-labelledby="sendBulkEmailSponsorModalLabel" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Form View -->
                <div id="sponsor-form-view" class="modal-view active">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sendBulkEmailSponsorModalLabel">Send Bulk Email to Sponsors</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Dynamic Recipient Count Display -->
                        <div class="alert alert-info py-2 mb-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>
                                    <i class="fas fa-envelope me-2"></i>
                                    <strong>Email Recipients:</strong>
                                    <span id="sponsor-recipient-count" class="badge bg-primary ms-2">0 recipients</span>
                                </span>
                                <span id="sponsor-filter-summary" class="text-muted small"></span>
                            </div>
                        </div>

                        <form id="bulkEmailForm" enctype="multipart/form-data">
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
                                <label for="sponsorFilterSelect" class="form-label">Filter By Custom Filter</label>
                                <select class="form-select form-select-sm" id="sponsorFilterSelect" name="sponsorFilter">
                                    @if (!empty($sponsor_filters))
                                        <option value="">Choose ...</option>
                                        @foreach ($sponsor_filters as $filter)
                                            <option value="{{ $filter->id }}">{{ $filter->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="sponsorSubject">Subject <small style="color:red;">*</small> </label>
                                <input type="text" class="form-control form-control-sm" name="subject"
                                    placeholder="PTA Meeting " id="sponsorSubject" required>
                            </div>

                            <div class="mb-3">
                                <label for="emailTextSponsor">Message <small style="color:red;">*</small></label>
                                <textarea id="emailTextSponsor" name="message" class="form-control form-control-sm" rows="4"
                                    placeholder="Type your message..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Attachment (Optional)</label>
                                <div class="custom-file-input">
                                    <input type="file" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                    <label for="attachment" class="file-input-label">
                                        <div class="file-input-icon">
                                            <i class="fas fa-paperclip"></i>
                                        </div>
                                        <div class="file-input-text">
                                            <span class="file-label">Choose File</span>
                                            <span class="file-hint">PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max 10MB)</span>
                                            <span class="file-name" id="sponsorFileName"></span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col d-flex justify-content-end">
                                    <button type="button" class="btn btn-sm btn-secondary me-2" data-bs-dismiss="modal">
                                        <i class="fas fa-times me-1"></i> Close
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary btn-loading" id="sendBulkEmailButton"
                                        disabled>
                                        <span class="btn-text"><i class="fas fa-paper-plane me-1"></i> Send Email</span>
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
                            <span id="sponsor-progress-title">Sending Email Messages...</span>
                        </h5>
                        <button type="button" class="btn-close" disabled>
                            <i class="fas fa-times"></i>
                        </button>
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
    <!-- end Bulk Email Modal -->

    <!-- Enhanced Bulk Email Modal for Staff with Dynamic Recipient Count -->
    <div class="modal fade" id="sendBulkEmailUserModal" tabindex="-1" aria-labelledby="sendBulkEmailUserModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Form View -->
                <div id="user-form-view" class="modal-view active">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sendBulkEmailUserModalLabel">Send Bulk Email to Staff</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Dynamic Recipient Count Display -->
                        <div class="alert alert-info py-2 mb-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>
                                    <i class="fas fa-envelope me-2"></i>
                                    <strong>Email Recipients:</strong>
                                    <span id="user-recipient-count" class="badge bg-primary ms-2">0 recipients</span>
                                </span>
                                <span id="user-filter-summary" class="text-muted small"></span>
                            </div>
                        </div>

                        <form id="bulkEmailUserForm" enctype="multipart/form-data">
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
                                <label for="filterSelect" class="form-label">Select By Custom Filter</label>
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
                                <label for="userSubject">Subject <small style="color:red">*</small></label>
                                <input type="text" name="userSubject" placeholder="Re: Staff Briefing"
                                    class="form-control form-control-sm" id="userSubject" required>
                            </div>
                            <div class="mb-3">
                                <label for="emailTextUser">Message <small style="color:red">*</small></label>
                                <textarea id="emailTextUser" name="message" class="form-control form-control-sm" rows="2"
                                    placeholder="Type your message..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Attachment (Optional)</label>
                                <div class="custom-file-input">
                                    <input type="file" id="attachmentUser" name="attachmentUser" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                    <label for="attachmentUser" class="file-input-label">
                                        <div class="file-input-icon">
                                            <i class="fas fa-paperclip"></i>
                                        </div>
                                        <div class="file-input-text">
                                            <span class="file-label">Choose File</span>
                                            <span class="file-hint">PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max 10MB)</span>
                                            <span class="file-name" id="userFileName"></span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col d-flex justify-content-end">
                                    <button type="button" class="btn btn-sm btn-secondary me-2" data-bs-dismiss="modal">
                                        <i class="fas fa-times me-1"></i> Close
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary btn-loading" id="sendBulkEmailUserButton"
                                        disabled>
                                        <span class="btn-text"><i class="fas fa-paper-plane me-1"></i> Send Email</span>
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
                            <span id="user-progress-title">Sending Email Messages...</span>
                        </h5>
                        <button type="button" class="btn-close" disabled>
                            <i class="fas fa-times"></i>
                        </button>
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
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/tooltipster/dist/js/tooltipster.bundle.min.js"></script>
    <script>
        // Cache for recipient counts
        let recipientCache = {
            sponsors: {},
            users: {}
        };

        // Modal view management
        let currentModalType = null; // 'sponsor' or 'user'

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
                $('#sendBulkEmailSponsorModal').modal('hide');
            } else {
                $('#sendBulkEmailUserModal').modal('hide');
            }

            // You can add background job indicator here if needed
            // For now, we'll just hide the modal
        }

        $(document).ready(function() {
            // ============================================
            // CKEditor Management
            // ============================================
            $('#sendBulkEmailSponsorModal').on('shown.bs.modal', function() {
                // Destroy existing editor first to prevent duplicates
                if (window.editorSponsor) {
                    window.editorSponsor.destroy().catch(() => {});
                    window.editorSponsor = null;
                }

                const textarea = document.querySelector('#emailTextSponsor');
                if (textarea && !textarea.classList.contains('ck-editor__editable')) {
                    ClassicEditor
                        .create(textarea)
                        .then(editor => {
                            window.editorSponsor = editor;
                        })
                        .catch(error => {
                            console.error('CKEditor initialization failed:', error);
                            // Show fallback message - the textarea will work without the rich editor
                            if (textarea) {
                                textarea.placeholder = 'Rich text editor unavailable. You can still type your message here.';
                                textarea.style.minHeight = '150px';
                            }
                        });
                }
                // Set initial filter summary
                document.getElementById('sponsor-filter-summary').textContent = 'All Grades | All Sponsors';
                // Update recipient count when modal opens
                updateSponsorRecipientCount();
                // Reset to form view
                switchModalView('sponsor', 'form');
            });

            $('#sendBulkEmailSponsorModal').on('hidden.bs.modal', function() {
                if (window.editorSponsor) {
                    window.editorSponsor.destroy()
                        .then(() => {
                            window.editorSponsor = null;
                        })
                        .catch(error => {
                            console.error(error);
                        });
                }
                // Reset form and switch back to form view
                document.getElementById('bulkEmailForm').reset();
                document.getElementById('sponsor-filter-summary').textContent = '';
                document.getElementById('sponsor-recipient-count').textContent = '0 recipients';
                document.getElementById('sponsorFileName').textContent = '';
                switchModalView('sponsor', 'form');
            });

            $('#sendBulkEmailUserModal').on('shown.bs.modal', function() {
                // Destroy existing editor first to prevent duplicates
                if (window.editorUser) {
                    window.editorUser.destroy().catch(() => {});
                    window.editorUser = null;
                }

                const textarea = document.querySelector('#emailTextUser');
                if (textarea && !textarea.classList.contains('ck-editor__editable')) {
                    ClassicEditor
                        .create(textarea)
                        .then(editor => {
                            window.editorUser = editor;
                        })
                        .catch(error => {
                            console.error('CKEditor initialization failed:', error);
                            // Show fallback message - the textarea will work without the rich editor
                            if (textarea) {
                                textarea.placeholder = 'Rich text editor unavailable. You can still type your message here.';
                                textarea.style.minHeight = '150px';
                            }
                        });
                }
                // Set initial filter summary
                document.getElementById('user-filter-summary').textContent = 'All Staff';
                // Update recipient count when modal opens
                updateUserRecipientCount();
                // Reset to form view
                switchModalView('user', 'form');
            });

            $('#sendBulkEmailUserModal').on('hidden.bs.modal', function() {
                if (window.editorUser) {
                    window.editorUser.destroy()
                        .then(() => {
                            window.editorUser = null;
                        })
                        .catch(error => {
                            console.error(error);
                        });
                }
                // Reset form and switch back to form view
                document.getElementById('bulkEmailUserForm').reset();
                document.getElementById('user-filter-summary').textContent = '';
                document.getElementById('user-recipient-count').textContent = '0 recipients';
                document.getElementById('userFileName').textContent = '';
                switchModalView('user', 'form');
            });

            // ============================================
            // File Input Display Handlers
            // ============================================
            document.getElementById('attachment').addEventListener('change', function() {
                const fileName = this.files[0] ? this.files[0].name : '';
                document.getElementById('sponsorFileName').textContent = fileName;
            });

            document.getElementById('attachmentUser').addEventListener('change', function() {
                const fileName = this.files[0] ? this.files[0].name : '';
                document.getElementById('userFileName').textContent = fileName;
            });

            // ============================================
            // Term Selection and Data Loading
            // ============================================
            $('#termId').change(function() {
                var term = $(this).val();
                var studentsSessionUrl = "{{ route('students.term-session') }}";
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
                // Your existing skeleton loader code
                var skeletonHTML = `<!-- Skeleton HTML -->`;
                $('#emails-term').html(skeletonHTML);
            }

            function fetchTermMessages() {
                var messagesByTermUrl = "{{ route('notifications.get-emails') }}";
                $.ajax({
                    url: messagesByTermUrl,
                    method: 'GET',
                    success: function(response) {
                        $('#emails-term').html(response);
                        // Update stats after content loads
                        setTimeout(function() {
                            updateHeaderStats();
                        }, 100);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching term data:", error);
                    }
                });
            }

            function updateHeaderStats() {
                // Count emails from the table
                var emailRows = $('#emails-table tbody tr').length;
                var hasEmptyState = $('#emails-table tbody tr td[colspan]').length > 0;
                var totalCount = hasEmptyState ? 0 : emailRows;
                $('#stat-total').text(totalCount);

                // Get bulk and individual counts from the overview cards
                var bulkText = $('.email-card-gradient-2 .email-card-value').text();
                var individualText = $('.email-card-gradient-3 .email-card-value').text();

                $('#stat-bulk').text(bulkText || '0');
                $('#stat-individual').text(individualText || '0');
            }

            $('#termId').trigger('change');
        });

        // ============================================
        // Cancel Button Event Listeners
        // ============================================
        document.getElementById('sponsor-cancel-btn').addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel sending emails?')) {
                // Reset to form view
                switchModalView('sponsor', 'form');
                enableSendButton(document.getElementById('sendBulkEmailButton'));
            }
        });

        document.getElementById('user-cancel-btn').addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel sending emails?')) {
                // Reset to form view
                switchModalView('user', 'form');
                enableSendButton(document.getElementById('sendBulkEmailUserButton'));
            }
        });

        // ============================================
        // Dynamic Recipient Count for Email Modals
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            // ============================================
            // Sponsor Modal Dynamic Count
            // ============================================
            let sponsorDebounceTimer;
            const sponsorFilters = ['gradeSelect', 'sponsorFilterSelect'];

            // Add event listeners to sponsor filters
            sponsorFilters.forEach(filterId => {
                const element = document.getElementById(filterId);
                if (element) {
                    element.addEventListener('change', () => {
                        clearTimeout(sponsorDebounceTimer);
                        sponsorDebounceTimer = setTimeout(() => updateSponsorRecipientCount(), 300);
                    });
                }
            });

            // Enable/disable send button based on subject
            document.getElementById('sponsorSubject').addEventListener('input', function() {
                validateSponsorForm();
            });

            function updateSponsorRecipientCount() {
                const grade = document.getElementById('gradeSelect').value;
                const sponsorFilter = document.getElementById('sponsorFilterSelect').value;

                // Create cache key
                const cacheKey = `${grade}_${sponsorFilter}`;

                // Check cache first
                if (recipientCache.sponsors[cacheKey] !== undefined) {
                    displaySponsorCount(recipientCache.sponsors[cacheKey]);
                    return;
                }

                // Create FormData for the request
                const formData = new FormData();
                formData.append('grade', grade);
                formData.append('sponsorFilter', sponsorFilter);
                formData.append('recipient_type', 'sponsor');

                // Make API call
                fetch("{{ route('notifications.check-email-recipients') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            recipientCache.sponsors[cacheKey] = data.count;
                            displaySponsorCount(data.count);
                        } else {
                            displaySponsorCount(0);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching recipient count:', error);
                        displaySponsorCount(0);
                    });
            }

            function displaySponsorCount(count) {
                const recipientDisplay = document.getElementById('sponsor-recipient-count');
                const filterSummary = document.getElementById('sponsor-filter-summary');

                recipientDisplay.textContent = count.toLocaleString() + ' recipient' + (count !== 1 ? 's' : '');

                // Update display styling
                if (count === 0) {
                    filterSummary.textContent = 'No recipients found with current filters';
                } else {
                    // Build filter summary
                    const grade = document.getElementById('gradeSelect').selectedOptions[0]?.text || 'All Grades';
                    const filter = document.getElementById('sponsorFilterSelect').selectedOptions[0]?.text ||
                        'All Sponsors';
                    filterSummary.textContent = `${grade} | ${filter}`;
                }

                validateSponsorForm();
            }

            function validateSponsorForm() {
                const sendButton = document.getElementById('sendBulkEmailButton');
                const subject = document.getElementById('sponsorSubject').value;
                const recipientCount = parseInt(document.getElementById('sponsor-recipient-count').textContent) ||
                    0;

                sendButton.disabled = !(recipientCount > 0 && subject.trim() !== '');
            }

            // ============================================
            // User/Staff Modal Dynamic Count
            // ============================================
            let userDebounceTimer;
            const userFilters = ['departmentSelect', 'areaOfWorkSelect', 'positionSelect', 'filterSelect'];

            // Add event listeners to user filters
            userFilters.forEach(filterId => {
                const element = document.getElementById(filterId);
                if (element) {
                    element.addEventListener('change', () => {
                        clearTimeout(userDebounceTimer);
                        userDebounceTimer = setTimeout(() => updateUserRecipientCount(), 300);
                    });
                }
            });

            // Enable/disable send button based on subject
            document.getElementById('userSubject').addEventListener('input', function() {
                validateUserForm();
            });

            function updateUserRecipientCount() {
                const department = document.getElementById('departmentSelect').value;
                const areaOfWork = document.getElementById('areaOfWorkSelect').value;
                const position = document.getElementById('positionSelect').value;
                const filter = document.getElementById('filterSelect').value;

                // Create cache key
                const cacheKey = `${department}_${areaOfWork}_${position}_${filter}`;

                // Check cache first
                if (recipientCache.users[cacheKey] !== undefined) {
                    displayUserCount(recipientCache.users[cacheKey]);
                    return;
                }

                // Create FormData for the request
                const formData = new FormData();
                formData.append('department', department);
                formData.append('area_of_work', areaOfWork);
                formData.append('position', position);
                formData.append('filter', filter);
                formData.append('recipient_type', 'user');

                // Make API call
                fetch("{{ route('notifications.check-email-recipients') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            recipientCache.users[cacheKey] = data.count;
                            displayUserCount(data.count);
                        } else {
                            displayUserCount(0);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching recipient count:', error);
                        displayUserCount(0);
                    });
            }

            function displayUserCount(count) {
                const recipientDisplay = document.getElementById('user-recipient-count');
                const filterSummary = document.getElementById('user-filter-summary');

                recipientDisplay.textContent = count.toLocaleString() + ' recipient' + (count !== 1 ? 's' : '');

                // Update display styling
                if (count === 0) {
                    filterSummary.textContent = 'No recipients found with current filters';
                } else {
                    // Build filter summary
                    const filters = [];
                    const dept = document.getElementById('departmentSelect').selectedOptions[0]?.text;
                    const area = document.getElementById('areaOfWorkSelect').selectedOptions[0]?.text;
                    const pos = document.getElementById('positionSelect').selectedOptions[0]?.text;

                    if (dept && dept !== 'All Departments') filters.push(dept);
                    if (area && area !== 'All Areas') filters.push(area);
                    if (pos && pos !== 'All Positions') filters.push(pos);

                    filterSummary.textContent = filters.length > 0 ? filters.join(' | ') : 'All Staff';
                }

                validateUserForm();
            }

            function validateUserForm() {
                const sendButton = document.getElementById('sendBulkEmailUserButton');
                const subject = document.getElementById('userSubject').value;
                const recipientCount = parseInt(document.getElementById('user-recipient-count').textContent) || 0;

                sendButton.disabled = !(recipientCount > 0 && subject.trim() !== '');
            }

            // ============================================
            // Send Button Handlers (Enhanced)
            // ============================================
            const sendBulkEmailButtonSponsor = document.getElementById('sendBulkEmailButton');
            const sendBulkEmailButtonUser = document.getElementById('sendBulkEmailUserButton');

            sendBulkEmailButtonSponsor.addEventListener('click', function() {
                const recipientCount = parseInt(document.getElementById('sponsor-recipient-count')
                    .textContent) || 0;

                if (recipientCount === 0) {
                    alert('No recipients found for the selected criteria.');
                    return;
                }

                disableSendButton(sendBulkEmailButtonSponsor);

                const subject = document.getElementById('sponsorSubject').value;
                // Get message from CKEditor or fallback to textarea value
                let message = '';
                try {
                    message = window.editorSponsor ? window.editorSponsor.getData() : document.getElementById('emailTextSponsor').value;
                } catch (e) {
                    message = document.getElementById('emailTextSponsor').value;
                    console.warn('CKEditor not available, using textarea value:', e);
                }
                const grade = document.getElementById('gradeSelect').value;
                const sponsorFilter = document.getElementById('sponsorFilterSelect').value;
                const attachment = document.getElementById('attachment').files[0];

                if (subject.trim() === '') {
                    alert('Please enter a subject.');
                    enableSendButton(sendBulkEmailButtonSponsor);
                    return;
                }

                if (message.trim() === '') {
                    alert('Please enter a message.');
                    enableSendButton(sendBulkEmailButtonSponsor);
                    return;
                }

                const confirmSend = confirm(
                    `You are about to send email to ${recipientCount} recipients. Do you want to proceed?`
                );
                if (!confirmSend) {
                    enableSendButton(sendBulkEmailButtonSponsor);
                    return;
                }

                // Switch to progress view
                switchModalView('sponsor', 'progress');

                // Set initial progress values
                document.getElementById('sponsor-total-recipients').textContent = recipientCount;
                document.getElementById('sponsor-sent-count').textContent = '0';
                document.getElementById('sponsor-failed-count').textContent = '0';
                document.getElementById('sponsor-progress-bar').style.width = '0%';
                document.getElementById('sponsor-progress-percentage').textContent = '0%';
                document.getElementById('sponsor-progress-details').textContent =
                    'Preparing to send emails...';

                const formData = new FormData();
                formData.append('subject', subject);
                formData.append('message', message);
                formData.append('grade', grade);
                formData.append('sponsorFilter', sponsorFilter);
                formData.append('recipient_type', 'sponsor');

                if (attachment) {
                    formData.append('attachment', attachment);
                }

                sendBulkEmail(formData, sendBulkEmailButtonSponsor, 'sponsor');
            });

            sendBulkEmailButtonUser.addEventListener('click', function() {
                const recipientCount = parseInt(document.getElementById('user-recipient-count')
                    .textContent) || 0;

                if (recipientCount === 0) {
                    alert('No recipients found for the selected criteria.');
                    return;
                }

                disableSendButton(sendBulkEmailButtonUser);

                const subject = document.querySelector('input[name="userSubject"]').value;
                // Get message from CKEditor or fallback to textarea value
                let message = '';
                try {
                    message = window.editorUser ? window.editorUser.getData() : document.getElementById('emailTextUser').value;
                } catch (e) {
                    message = document.getElementById('emailTextUser').value;
                    console.warn('CKEditor not available, using textarea value:', e);
                }
                const department = document.getElementById('departmentSelect').value;
                const areaOfWork = document.getElementById('areaOfWorkSelect').value;
                const position = document.getElementById('positionSelect').value;
                const filter = document.getElementById('filterSelect').value;
                const attachment = document.getElementById('attachmentUser').files[0];

                if (subject.trim() === '') {
                    alert('Please enter a subject.');
                    enableSendButton(sendBulkEmailButtonUser);
                    return;
                }

                if (message.trim() === '') {
                    alert('Please enter a message.');
                    enableSendButton(sendBulkEmailButtonUser);
                    return;
                }

                const confirmSend = confirm(
                    `You are about to send email to ${recipientCount} recipients. Do you want to proceed?`
                );
                if (!confirmSend) {
                    enableSendButton(sendBulkEmailButtonUser);
                    return;
                }

                // Switch to progress view
                switchModalView('user', 'progress');

                // Set initial progress values
                document.getElementById('user-total-recipients').textContent = recipientCount;
                document.getElementById('user-sent-count').textContent = '0';
                document.getElementById('user-failed-count').textContent = '0';
                document.getElementById('user-progress-bar').style.width = '0%';
                document.getElementById('user-progress-percentage').textContent = '0%';
                document.getElementById('user-progress-details').textContent =
                    'Preparing to send emails...';

                const formData = new FormData();
                formData.append('subject', subject);
                formData.append('message', message);
                formData.append('department', department);
                formData.append('area_of_work', areaOfWork);
                formData.append('position', position);
                formData.append('filter', filter);
                formData.append('recipient_type', 'user');

                if (attachment) {
                    formData.append('attachment', attachment);
                }

                sendBulkEmail(formData, sendBulkEmailButtonUser, 'user');
            });

            function sendBulkEmail(formData, sendButton, modalType) {
                const url = "{{ route('notifications.send-bulk-email') }}";

                // Simulate progress updates (you can replace this with real progress tracking)
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress > 90) progress = 90;

                    const progressBar = document.getElementById(`${modalType}-progress-bar`);
                    const progressPercentage = document.getElementById(`${modalType}-progress-percentage`);
                    const progressDetails = document.getElementById(`${modalType}-progress-details`);

                    progressBar.style.width = progress + '%';
                    progressPercentage.textContent = Math.round(progress) + '%';

                    if (progress < 30) {
                        progressDetails.textContent = 'Connecting to email server...';
                    } else if (progress < 60) {
                        progressDetails.textContent = 'Sending emails...';
                    } else if (progress < 90) {
                        progressDetails.textContent = 'Finalizing...';
                    }
                }, 500);

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        clearInterval(progressInterval);

                        // Complete progress
                        const progressBar = document.getElementById(`${modalType}-progress-bar`);
                        const progressPercentage = document.getElementById(`${modalType}-progress-percentage`);
                        const progressDetails = document.getElementById(`${modalType}-progress-details`);

                        progressBar.style.width = '100%';
                        progressPercentage.textContent = '100%';

                        if (data.success) {
                            progressDetails.textContent = 'Emails sent successfully!';
                            progressBar.classList.remove('bg-info');
                            progressBar.classList.add('bg-success');

                            // Update counts
                            const totalRecipients = parseInt(document.getElementById(
                                `${modalType}-total-recipients`).textContent);
                            document.getElementById(`${modalType}-sent-count`).textContent = totalRecipients;
                            document.getElementById(`${modalType}-failed-count`).textContent = '0';

                            setTimeout(() => {
                                // Close modal and refresh
                                $(`#sendBulkEmail${modalType.charAt(0).toUpperCase() + modalType.slice(1)}Modal`)
                                    .modal('hide');
                                $('#termId').trigger('change');
                            }, 2000);
                        } else {
                            progressDetails.textContent = 'Error: ' + data.message;
                            progressBar.classList.remove('bg-info');
                            progressBar.classList.add('bg-danger');
                        }

                        enableSendButton(sendButton);
                    })
                    .catch(error => {
                        clearInterval(progressInterval);

                        const progressBar = document.getElementById(`${modalType}-progress-bar`);
                        const progressDetails = document.getElementById(`${modalType}-progress-details`);

                        progressBar.classList.remove('bg-info');
                        progressBar.classList.add('bg-danger');
                        progressDetails.textContent = 'An error occurred while sending emails.';

                        enableSendButton(sendButton);
                        console.error('Error:', error);
                    });
            }

            function disableSendButton(button) {
                button.disabled = true;
                button.classList.add('loading');
            }

            function enableSendButton(button) {
                button.disabled = false;
                button.classList.remove('loading');
            }
        });
    </script>
@endsection

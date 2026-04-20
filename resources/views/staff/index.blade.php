@extends('layouts.master')
@section('title')
    Human Resources
@endsection
@section('css')
    <style>
        .staff-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .staff-header {
            background: linear-gradient(135deg, #5b6ef7 0%, #8b5cf6 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .staff-body {
            padding: 24px;
        }

        #usersList {
            transition: opacity 0.2s ease;
        }

        /* Hide DataTables default controls */
        .dataTables_length,
        .dataTables_filter {
            display: none !important;
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

        .help-text {
            background: rgba(91, 110, 247, 0.05);
            padding: 16px 20px;
            border-radius: 3px;
            border-left: 3px solid #5b6ef7;
            margin-bottom: 20px;
        }

        .help-title {
            font-weight: 600;
            font-size: 14px;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
        }

        .gender-male {
            color: #007bff;
        }

        .gender-female {
            color: #e83e8c;
        }

        .avatar-circle {
            width: 65px;
            height: 65px;
            overflow: hidden;
            border-radius: 50%;
        }

        .avatar-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-placeholder {
            width: 100%;
            height: 100%;
            background-color: #e0e0e0;
            color: #757575;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
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

        .btn-primary {
            background: linear-gradient(135deg, #5b6ef7 0%, #8b5cf6 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #4f5de4 0%, #7c3aed 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(91, 110, 247, 0.3);
            color: white;
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

        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #5b6ef7;
            box-shadow: 0 0 0 3px rgba(91, 110, 247, 0.1);
        }

        /* Reports Dropdown Styling */
        .reports-dropdown .dropdown-toggle {
            background: linear-gradient(135deg, #5b6ef7 0%, #8b5cf6 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .reports-dropdown .dropdown-toggle:hover {
            background: linear-gradient(135deg, #4f5de4 0%, #7c3aed 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(91, 110, 247, 0.3);
            color: white;
        }

        .reports-dropdown .dropdown-toggle:focus {
            box-shadow: 0 0 0 3px rgba(91, 110, 247, 0.3);
            color: white;
        }

        .reports-dropdown .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 3px;
            padding: 8px 0;
            min-width: 220px;
            margin-top: 4px;
        }

        .reports-dropdown .dropdown-item {
            padding: 8px 16px;
            font-size: 14px;
            color: #374151;
        }

        .reports-dropdown .dropdown-item:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .reports-dropdown .dropdown-item i {
            width: 20px;
            margin-right: 8px;
        }

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

        /* Modal Styling */
        .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 24px;
        }

        .modal-header .modal-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            border-top: 1px solid #f3f4f6;
            padding: 16px 24px;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .staff-header {
                padding: 20px;
            }

            .staff-body {
                padding: 16px;
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

        /* Ensure modal footer buttons are same size */
        .modal-footer .btn {
            display: inline-flex;
            align-items: center;
        }

        /* Hide original textarea when CKEditor is active */
        #email-body.ck-hidden {
            display: none !important;
        }

        /* Ensure only one CKEditor instance is visible */
        .ck.ck-editor + .ck.ck-editor {
            display: none !important;
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
            border-color: #5b6ef7;
            background: #f0f4ff;
        }

        .file-input-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #5b6ef7 0%, #8b5cf6 100%);
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            flex-shrink: 0;
        }

        .file-input-text {
            flex: 1;
        }

        .file-input-text .file-label {
            font-weight: 500;
            color: #374151;
            display: block;
            margin-bottom: 2px;
        }

        .file-input-text .file-hint {
            font-size: 13px;
            color: #6b7280;
        }

        .file-input-text .file-selected {
            font-size: 13px;
            color: #5b6ef7;
            font-weight: 500;
        }
    </style>
    @include('layouts.partials.pagination-rounded')
@endsection
@section('content')
    @if (session('message'))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <div class="staff-container">
        <div class="staff-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;"><i class="fas fa-users me-2"></i>Human Resources</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Browse and manage all staff members</p>
                </div>
                <div class="col-md-6">
                    @php
                        $totalCount = $users->count() ?? 0;
                        $maleCount = $users->where('gender', 'M')->count() ?? 0;
                        $femaleCount = $users->where('gender', 'F')->count() ?? 0;
                    @endphp
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalCount }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $maleCount }}</h4>
                                <small class="opacity-75">Male</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $femaleCount }}</h4>
                                <small class="opacity-75">Female</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="staff-body">
            <div class="help-text">
                <div class="help-title">Staff Directory</div>
                <div class="help-content">
                    Browse and manage all staff members. Use the filters below to find specific staff by status, position, or department. Click on a staff member to view their full profile.
                </div>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" id="name-search" class="form-control" placeholder="Search by name or email...">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <select id="status-filter" name="status" class="form-select">
                                    <option value="">All Status</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->id }}"
                                            {{ request('status') == $status->id || (!request('status') && $status->name === 'Current') ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <select id="user-position-filter" name="position" class="form-select">
                                    <option value="">All Positions</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}">{{ $position->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <select id="department-filter" name="department" class="form-select">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-12">
                                <button type="button" class="btn btn-light w-100" id="clear-filters">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                        @can('manage-hr')
                            <a href="{{ route('staff.staff-new') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> New Staff
                            </a>
                        @endcan
                        <div class="btn-group reports-dropdown">
                            <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2" style="font-size: 10px;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('staff.staff-custom-analysis') }}">
                                        <i class="fas fa-cogs me-2"></i> Staff Custom Report
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('staff.analysis-report') }}">
                                        <i class="fas fa-chart-line me-2"></i> Staff Analysis Report
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('staff.analysis-area-of-work') }}">
                                        <i class="fas fa-briefcase me-2"></i> Staff Analysis by Position & Nationality
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('staff.user-qualifications') }}">
                                        <i class="fas fa-graduation-cap me-2"></i> Staff by Qualifications Report
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('staff.qualifications') }}">
                                        <i class="fas fa-award me-2"></i> Staff Analysis by Qualifications
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('staff.analysis-by-role') }}">
                                        <i class="fas fa-user-tag me-2"></i> Staff Analysis by Roles
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('staff.staff-usersByNationality') }}">
                                        <i class="fas fa-globe me-2"></i> Staff Analysis by Nationality
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('staff.analysis-department') }}">
                                        <i class="fas fa-building me-2"></i> Staff Analysis by Department
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('staff.staff-by-filters') }}">
                                        <i class="fas fa-filter me-2"></i> Staff Analysis by Filters
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('staff.organisational-reporting') }}">
                                        <i class="fas fa-sitemap me-2"></i> Staff Organisation Chart
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div id="usersList">
                @include('staff.partials.users-list', ['users' => $users])
            </div>

            @php
                $smsEnabled = $communicationChannels['sms_enabled'] ?? false;
                $whatsappEnabled = $communicationChannels['whatsapp_enabled'] ?? false;
            @endphp

            @if ($smsEnabled)
            <div class="modal fade" id="sendSmsModal" tabindex="-1" aria-labelledby="sendSmsModalLabel" aria-hidden="true"
                data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="sendSmsModalLabel">Send SMS</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <label for="smsText" class="form-label">Message</label>
                            <textarea id="smsText" name="message" class="form-control" rows="4"
                                placeholder="Type your SMS message..."></textarea>
                            <div id="smsCharCount" class="mt-2">Characters: 0 | SMS Count: 0</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancel
                            </button>
                            <button type="button" class="btn btn-primary btn-loading" id="sendDirectSmsButton">
                                <span class="btn-text"><i class="fas fa-paper-plane me-1"></i>Send SMS</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Sending...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if ($whatsappEnabled)
            <div class="modal fade" id="sendWhatsappModal" tabindex="-1" aria-labelledby="sendWhatsappModalLabel" aria-hidden="true"
                data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="sendWhatsappModalLabel">Send WhatsApp</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info py-2">
                                WhatsApp sends use approved templates only. Consent is required before sending.
                            </div>
                            <div class="mb-3">
                                <label for="directWhatsappTemplate" class="form-label">Template</label>
                                <select id="directWhatsappTemplate" class="form-select">
                                    <option value="">Select a WhatsApp template...</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Template Preview</label>
                                <div id="directWhatsappTemplatePreview" class="form-control bg-light" style="min-height: 90px;">Choose a template to preview its content.</div>
                            </div>
                            <div id="directWhatsappVariables"></div>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" value="1" id="directWhatsappConsent">
                                <label class="form-check-label" for="directWhatsappConsent">
                                    Record consent with this send if it has already been received
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancel
                            </button>
                            <button type="button" class="btn btn-success btn-loading" id="sendDirectWhatsappButton">
                                <span class="btn-text"><i class="fas fa-paper-plane me-1"></i>Send WhatsApp</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Sending...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true"
                data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="sendEmailModalLabel">Compose Email</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="sendEmailForm" method="POST" action="{{ route('notifications.send-email') }}"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" id="receiver-id" name="receiver_id">
                                <input type="hidden" id="receiver-type" name="receiver_type">
                                <div class="mb-3">
                                    <label for="recipient-email" class="form-label">To</label>
                                    <input type="email" class="form-control" id="recipient-email"
                                        name="recipient_email" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="email-subject" class="form-label">Subject</label>
                                    <input type="text" placeholder="Enter Subject ..." class="form-control"
                                        id="email-subject" name="subject" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email-body" class="form-label">Message</label>
                                    <textarea placeholder="Email body here ..." class="form-control" cols="10" rows="10" id="email-body"
                                        name="body"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Attachment <small style="color:red;">(Cannot exceed 8MB)</small></label>
                                    <div class="custom-file-input">
                                        <input type="file" id="email-attachment" name="attachment">
                                        <label for="email-attachment" class="file-input-label">
                                            <div class="file-input-icon">
                                                <i class="fas fa-paperclip"></i>
                                            </div>
                                            <div class="file-input-text">
                                                <span class="file-label">Choose Attachment</span>
                                                <span class="file-hint" id="attachment-hint">PDF, DOC, XLS, images, etc.</span>
                                                <span class="file-selected d-none" id="attachment-name"></span>
                                            </div>
                                        </label>
                                    </div>
                                    <small id="attachment-error" class="text-danger" style="display: none;">File size
                                        exceeds 10MB limit.</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-primary btn-loading" id="send-email-btn">
                                    <span class="btn-text"><i class="fas fa-paper-plane me-1"></i>Send Email</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Sending...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            let searchTimeout = null;

            function loadFiltersFromLocalStorage() {
                let savedName = localStorage.getItem('filter_name_users');
                let savedStatus = localStorage.getItem('filter_status_users');
                let savedPosition = localStorage.getItem('filter_position_users');
                let savedDepartment = localStorage.getItem('filter_department_users');

                if (savedName !== null) {
                    $('#name-search').val(savedName);
                }
                if (savedStatus !== null) {
                    $('#status-filter').val(savedStatus);
                }
                if (savedPosition !== null) {
                    $('#user-position-filter').val(savedPosition);
                }
                if (savedDepartment !== null) {
                    $('#department-filter').val(savedDepartment);
                }
            }

            function saveFiltersToLocalStorage() {
                localStorage.setItem('filter_name_users', $('#name-search').val());
                localStorage.setItem('filter_status_users', $('#status-filter').val());
                localStorage.setItem('filter_position_users', $('#user-position-filter').val());
                localStorage.setItem('filter_department_users', $('#department-filter').val());
            }

            function initializeDataTable() {
                if ($.fn.DataTable.isDataTable('#datatable-icons')) {
                    $('#datatable-icons').DataTable().destroy();
                }
                $('#datatable-icons').DataTable({
                    searching: false,
                    lengthChange: false,
                    pageLength: 10,
                    language: {
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'></i>",
                            next: "<i class='mdi mdi-chevron-right'></i>"
                        }
                    },
                    drawCallback: function() {
                        $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                    }
                });
            }

            function initializeTooltips() {
                var existingTooltips = document.querySelectorAll(
                    '#datatable-icons [data-bs-toggle="tooltip"], #datatable-icons [data-bs-custom-tooltip="true"]');
                existingTooltips.forEach(function(el) {
                    var tooltip = bootstrap.Tooltip.getInstance(el);
                    if (tooltip) {
                        tooltip.dispose();
                    }
                });

                var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                    '#datatable-icons [data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                var modalTooltipTriggerList = [].slice.call(document.querySelectorAll(
                    '#datatable-icons [data-bs-custom-tooltip="true"]'));
                modalTooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            function fetchUsersData() {
                let nameVal = $('#name-search').val();
                let statusVal = $('#status-filter').val();
                let positionVal = $('#user-position-filter').val();
                let departmentVal = $('#department-filter').val();

                // Fade out existing content for smooth transition
                $('#usersList').css('opacity', '0.5');

                $.ajax({
                    url: "{{ route('staff.index') }}",
                    method: 'GET',
                    data: {
                        name: nameVal,
                        status: statusVal,
                        position: positionVal,
                        department: departmentVal
                    },
                    success: function(response) {
                        if ($.fn.DataTable.isDataTable('#datatable-icons')) {
                            $('#datatable-icons').DataTable().destroy();
                        }
                        $('#usersList').html(response.tableHtml);
                        updateStaffBadges(response);
                        initializeDataTable();
                        initializeTooltips();
                        $('#usersList').css('opacity', '1');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching users data:', {
                            httpStatus: xhr.status,
                            statusText: xhr.statusText,
                            readyState: xhr.readyState,
                            responseText: (xhr.responseText || '').substring(0, 500),
                            ajaxStatus: status,
                            ajaxError: error
                        });

                        // If the session expired, bounce to login instead of showing a dead-end
                        if (xhr.status === 401 || xhr.status === 419) {
                            window.location.reload();
                            return;
                        }

                        const detail = xhr.status
                            ? `(HTTP ${xhr.status} ${xhr.statusText || ''})`
                            : '(network error)';

                        $('#usersList').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                Failed to load users data ${detail}. Please try again.
                            </div>
                        `);
                        $('#usersList').css('opacity', '1');
                    }
                });
            }

            function updateStaffBadges(data) {
                const statElements = document.querySelectorAll('.stat-item h4');
                if (statElements.length >= 1) {
                    statElements[0].textContent = data.totalStaff || 0;
                }
            }

            // Name search with debounce
            $('#name-search').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    saveFiltersToLocalStorage();
                    fetchUsersData();
                }, 300);
            });

            $('#status-filter, #user-position-filter, #department-filter').change(function() {
                saveFiltersToLocalStorage();
                fetchUsersData();
            });

            $('#clear-filters').click(function() {
                $('#name-search').val('');
                $('#status-filter').val('');
                $('#user-position-filter').val('');
                $('#department-filter').val('');
                localStorage.removeItem('filter_name_users');
                localStorage.removeItem('filter_status_users');
                localStorage.removeItem('filter_position_users');
                localStorage.removeItem('filter_department_users');
                fetchUsersData();
            });

            loadFiltersFromLocalStorage();

            // Check if any saved filters differ from defaults — if so, fetch filtered data
            let hasActiveFilters = $('#name-search').val() ||
                ($('#status-filter').val() && $('#status-filter').val() !== '') ||
                $('#user-position-filter').val() ||
                $('#department-filter').val();

            if (hasActiveFilters) {
                fetchUsersData();
            } else {
                initializeDataTable();
                initializeTooltips();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const sendSmsModal = document.getElementById('sendSmsModal');
            if (sendSmsModal) {
                const smsText = document.getElementById('smsText');
                const smsCharCount = document.getElementById('smsCharCount');
                const sendDirectSmsButton = document.getElementById('sendDirectSmsButton');
                let smsRecipientId = null;
                let smsRecipientType = null;

                function updateSmsCount() {
                    const text = smsText.value;
                    const length = text.length;
                    const smsCount = length > 0 ? Math.ceil(length / 160) : 0;
                    smsCharCount.textContent = `Characters: ${length} | SMS Count: ${smsCount}`;
                }

                $('#sendSmsModal').on('show.bs.modal', function(event) {
                    const button = $(event.relatedTarget);
                    smsRecipientId = button.data('recipient-id');
                    smsRecipientType = button.data('recipient-type');
                });

                $('#sendSmsModal').on('hidden.bs.modal', function() {
                    sendDirectSmsButton.classList.remove('loading');
                    sendDirectSmsButton.disabled = false;
                    smsText.value = '';
                    updateSmsCount();
                });

                smsText.addEventListener('input', updateSmsCount);
                updateSmsCount();

                sendDirectSmsButton.addEventListener('click', function() {
                    const message = smsText.value.trim();
                    if (!message) {
                        alert('Please enter an SMS message.');
                        return;
                    }

                    sendDirectSmsButton.classList.add('loading');
                    sendDirectSmsButton.disabled = true;

                    const urlTemplate = "{{ route('send.sms', ['recipientType' => ':recipientType', 'id' => ':id']) }}";
                    const url = urlTemplate.replace(':recipientType', smsRecipientType).replace(':id', smsRecipientId);

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ message })
                    })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || 'Failed to send SMS.');
                        }
                        return data;
                    })
                    .then(data => {
                        sendDirectSmsButton.classList.remove('loading');
                        sendDirectSmsButton.disabled = false;
                        $('#sendSmsModal').modal('hide');
                        alert(data.message || 'SMS sent successfully.');
                    })
                    .catch(error => {
                        sendDirectSmsButton.classList.remove('loading');
                        sendDirectSmsButton.disabled = false;
                        alert(error.message || 'Error sending SMS. Please try again.');
                    });
                });
            }

            const sendWhatsappModal = document.getElementById('sendWhatsappModal');
            if (sendWhatsappModal) {
                const directWhatsappTemplate = document.getElementById('directWhatsappTemplate');
                const directWhatsappTemplatePreview = document.getElementById('directWhatsappTemplatePreview');
                const directWhatsappVariables = document.getElementById('directWhatsappVariables');
                const directWhatsappConsent = document.getElementById('directWhatsappConsent');
                const sendDirectWhatsappButton = document.getElementById('sendDirectWhatsappButton');
                let whatsappRecipientId = null;
                let whatsappRecipientType = null;
                let whatsappTemplates = [];

                function renderWhatsappVariables() {
                    const selectedId = directWhatsappTemplate.value;
                    const template = whatsappTemplates.find(item => String(item.id) === String(selectedId));

                    directWhatsappVariables.innerHTML = '';
                    directWhatsappTemplatePreview.textContent = template?.body_preview || 'Choose a template to preview its content.';

                    if (!template || !template.variables) {
                        return;
                    }

                    Object.keys(template.variables).forEach((key) => {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'mb-3';
                        wrapper.innerHTML = `
                            <label class="form-label" for="whatsapp-variable-${key}">${key}</label>
                            <input type="text" class="form-control whatsapp-template-variable" id="whatsapp-variable-${key}" data-variable-key="${key}" placeholder="Enter ${key}">
                        `;
                        directWhatsappVariables.appendChild(wrapper);
                    });
                }

                function loadWhatsappTemplates() {
                    fetch('{{ route("whatsapp-templates.api.list") }}')
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                return;
                            }

                            whatsappTemplates = data.templates || [];
                            let options = '<option value="">Select a WhatsApp template...</option>';
                            whatsappTemplates.forEach((template) => {
                                options += `<option value="${template.id}">${template.name} (${template.language})</option>`;
                            });
                            directWhatsappTemplate.innerHTML = options;
                        })
                        .catch(error => console.error('Failed to load WhatsApp templates:', error));
                }

                $('#sendWhatsappModal').on('show.bs.modal', function(event) {
                    const button = $(event.relatedTarget);
                    whatsappRecipientId = button.data('recipient-id');
                    whatsappRecipientType = button.data('recipient-type');
                    loadWhatsappTemplates();
                });

                $('#sendWhatsappModal').on('hidden.bs.modal', function() {
                    sendDirectWhatsappButton.classList.remove('loading');
                    sendDirectWhatsappButton.disabled = false;
                    directWhatsappTemplate.value = '';
                    directWhatsappTemplatePreview.textContent = 'Choose a template to preview its content.';
                    directWhatsappVariables.innerHTML = '';
                    directWhatsappConsent.checked = false;
                });

                directWhatsappTemplate.addEventListener('change', renderWhatsappVariables);

                sendDirectWhatsappButton.addEventListener('click', function() {
                    const templateId = directWhatsappTemplate.value;
                    if (!templateId) {
                        alert('Please select a WhatsApp template.');
                        return;
                    }

                    const variables = {};
                    directWhatsappVariables.querySelectorAll('.whatsapp-template-variable').forEach((field) => {
                        variables[field.dataset.variableKey] = field.value;
                    });

                    sendDirectWhatsappButton.classList.add('loading');
                    sendDirectWhatsappButton.disabled = true;

                    const urlTemplate = "{{ route('staff.send-message', ['recipientType' => ':recipientType', 'id' => ':id']) }}";
                    const url = urlTemplate.replace(':recipientType', whatsappRecipientType).replace(':id', whatsappRecipientId);

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            channel: 'whatsapp',
                            template_id: templateId,
                            template_variables: variables,
                            record_consent: directWhatsappConsent.checked ? 1 : 0,
                            consent_source: 'staff_admin'
                        })
                    })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || 'Failed to send WhatsApp message.');
                        }
                        return data;
                    })
                    .then(data => {
                        sendDirectWhatsappButton.classList.remove('loading');
                        sendDirectWhatsappButton.disabled = false;
                        $('#sendWhatsappModal').modal('hide');
                        alert(data.message || 'WhatsApp message queued successfully.');
                    })
                    .catch(error => {
                        sendDirectWhatsappButton.classList.remove('loading');
                        sendDirectWhatsappButton.disabled = false;
                        alert(error.message || 'Error sending WhatsApp message. Please try again.');
                    });
                });
            }

            let emailEditor = null;

            function destroyEmailEditor() {
                return new Promise((resolve) => {
                    if (emailEditor) {
                        emailEditor.destroy()
                            .then(() => {
                                emailEditor = null;
                                resolve();
                            })
                            .catch(() => {
                                emailEditor = null;
                                resolve();
                            });
                    } else {
                        resolve();
                    }
                });
            }

            function initEmailEditor() {
                const textarea = document.querySelector('#email-body');
                if (!textarea) return;

                // Check if CKEditor already exists
                if (emailEditor) {
                    return; // Editor already exists
                }

                // Remove any orphaned CKEditor elements
                const existingEditorElement = document.querySelector('#sendEmailModal .ck-editor');
                if (existingEditorElement) {
                    existingEditorElement.remove();
                    textarea.style.display = '';
                }

                ClassicEditor
                    .create(textarea)
                    .then(newEditor => {
                        emailEditor = newEditor;
                    })
                    .catch(error => {
                        console.error('CKEditor error:', error);
                    });
            }

            document.getElementById('sendEmailForm').addEventListener('submit', function(event) {
                // Get data from CKEditor if available
                const textarea = document.querySelector('#email-body');
                let bodyContent = '';

                if (emailEditor) {
                    try {
                        textarea.value = emailEditor.getData();
                        bodyContent = textarea.value.replace(/<[^>]*>/g, '').trim();
                    } catch (e) {
                        console.error('Error getting CKEditor data:', e);
                    }
                }

                // Fallback: get content directly from CKEditor's editable element
                if (!bodyContent) {
                    const ckEditable = document.querySelector('#sendEmailModal .ck-editor__editable');
                    if (ckEditable) {
                        bodyContent = ckEditable.textContent.trim();
                        textarea.value = ckEditable.innerHTML;
                    } else {
                        bodyContent = textarea.value.replace(/<[^>]*>/g, '').trim();
                    }
                }

                // Check if body is empty
                if (bodyContent === '') {
                    alert('The message body cannot be empty.');
                    event.preventDefault();
                    return false;
                }

                // Add loading state to button
                const sendBtn = document.getElementById('send-email-btn');
                sendBtn.classList.add('loading');
                sendBtn.disabled = true;
            });

            // Initialize CKEditor when modal is shown
            $('#sendEmailModal').on('shown.bs.modal', function(event) {
                var button = $(event.relatedTarget);

                var recipientEmail = button.data('recipient-email');
                var receiverId = button.data('recipient-id');
                var receiverType = button.data('recipient-type');

                var modal = $(this);
                modal.find('.modal-body input#recipient-email').val(recipientEmail);
                modal.find('.modal-body input#receiver-id').val(receiverId);
                modal.find('.modal-body input#receiver-type').val(receiverType);

                // Initialize CKEditor
                initEmailEditor();
            });

            // Destroy CKEditor when modal is hidden
            $('#sendEmailModal').on('hidden.bs.modal', function() {
                destroyEmailEditor();

                // Reset form
                document.getElementById('sendEmailForm').reset();

                // Reset button state
                const sendBtn = document.getElementById('send-email-btn');
                sendBtn.classList.remove('loading');
                sendBtn.disabled = false;

                // Reset file input visual state
                const attachmentHint = document.getElementById('attachment-hint');
                const attachmentName = document.getElementById('attachment-name');
                if (attachmentHint) attachmentHint.classList.remove('d-none');
                if (attachmentName) {
                    attachmentName.classList.add('d-none');
                    attachmentName.textContent = '';
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const attachmentInput = document.getElementById('email-attachment');
            const attachmentError = document.getElementById('attachment-error');
            const attachmentHint = document.getElementById('attachment-hint');
            const attachmentName = document.getElementById('attachment-name');
            const sendButton = document.getElementById('send-email-btn');
            const maxSize = 10 * 1024 * 1024;
            const warningThreshold = 9.5 * 1024 * 1024;

            attachmentInput.addEventListener('change', function() {
                if (this.files[0]) {
                    const file = this.files[0];
                    const fileSize = file.size;
                    const fileSizeMB = fileSize / 1024 / 1024;

                    // Show file name, hide hint
                    attachmentHint.classList.add('d-none');
                    attachmentName.classList.remove('d-none');
                    attachmentName.textContent = `${file.name} (${fileSizeMB.toFixed(2)} MB)`;

                    if (fileSize > maxSize) {
                        attachmentError.style.display = 'block';
                        attachmentError.textContent =
                            'File size exceeds 10MB limit. Please choose a smaller file.';
                        sendButton.disabled = true;
                    } else if (fileSize > warningThreshold) {
                        attachmentError.style.display = 'block';
                        attachmentError.textContent =
                            'File size is close to the 10MB limit. Upload may fail.';
                        sendButton.disabled = false;
                    } else {
                        attachmentError.style.display = 'none';
                        sendButton.disabled = false;
                    }
                } else {
                    // Hide file name, show hint
                    attachmentHint.classList.remove('d-none');
                    attachmentName.classList.add('d-none');
                    attachmentName.textContent = '';
                    attachmentError.style.display = 'none';
                    sendButton.disabled = false;
                }
            });

            document.getElementById('sendEmailForm').addEventListener('submit', function(e) {
                if (attachmentInput.files[0] && attachmentInput.files[0].size > maxSize) {
                    e.preventDefault();
                    alert('Attachment size exceeds 10MB limit. Please choose a smaller file.');
                }
            });
        });
    </script>
@endsection

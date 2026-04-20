@extends('layouts.master')
@section('title')
    Parents Module
@endsection
@section('css')
    <style>
        .gender-male {
            color: #007bff;
        }

        .gender-female {
            color: #e83e8c;
        }

        #sponsorsList {
            transition: opacity 0.2s ease;
        }

        /* Hide DataTables default controls */
        .dataTables_length,
        .dataTables_filter {
            display: none !important;
        }

        /* Table Header Styling from Admissions Index */
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

        /* Container Structure */
        .admissions-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .admissions-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .admissions-body {
            padding: 24px;
        }

        .stat-item {
            padding: 10px 0;
            text-align: center;
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

        /* Help Text */
        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        /* Filter Controls */
        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .controls .form-control:focus,
        .controls .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .controls .input-group-text {
            background: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 3px 0 0 3px;
            color: #6b7280;
        }

        .controls .input-group .form-control {
            border-radius: 0 3px 3px 0;
        }

        .controls #clear-filters {
            justify-content: center;
            min-width: 110px;
            white-space: nowrap;
        }

        .btn-light {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-light:hover {
            background: #e9ecef;
            color: #495057;
            transform: translateY(-1px);
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
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

        /* Reports Dropdown Styling */
        .reports-dropdown .dropdown-toggle {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .reports-dropdown .dropdown-toggle:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .reports-dropdown .dropdown-toggle:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .reports-dropdown .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 3px;
            padding: 8px 0;
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

        .reports-dropdown .dropdown-divider {
            margin: 8px 0;
        }

        .reports-dropdown .dropdown-header {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            padding: 8px 16px;
        }

        @media (max-width: 768px) {
            .admissions-header {
                padding: 20px;
            }
            .admissions-body {
                padding: 16px;
            }
            .stat-item h4 {
                font-size: 1.25rem;
            }
            .stat-item small {
                font-size: 0.75rem;
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
            border-color: #3b82f6;
            background: #f0f9ff;
        }

        .file-input-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 3px;
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
            color: #3b82f6;
            font-weight: 500;
        }
    </style>
    @include('layouts.partials.pagination-rounded')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Parents
        @endslot
        @slot('title')
            Sponsors List
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
    <div class="admissions-container">
        <div class="admissions-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Parents / Sponsors</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Browse and manage parent/sponsor records</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $sponsors->where('status', 'Current')->count() }}</h4>
                                <small class="opacity-75">Current</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $sponsors->where('gender', 'M')->count() }}</h4>
                                <small class="opacity-75">Male</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $sponsors->where('gender', 'F')->count() }}</h4>
                                <small class="opacity-75">Female</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admissions-body">
            <div class="help-text">
                <div class="help-title">Parent/Sponsor Directory</div>
                <div class="help-content">
                    Browse and manage all parent/sponsor records. Use filters to search by status, grade, or custom filters.
                </div>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search by name..." id="name-search">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
                                <select id="status-filter" class="form-select">
                                    <option value="Current" selected>Current</option>
                                    <option value="Past">Past</option>
                                    <option value="Deleted">Deleted</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
                                <select id="sponsor-filter" class="form-select">
                                    <option value="">All Filters</option>
                                    @foreach ($filters as $filter)
                                        <option value="{{ $filter->id }}">{{ $filter->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
                                <select id="grade-filter" class="form-select">
                                    <option value="">All Grades</option>
                                    @foreach ($grades as $grade)
                                        <option value="{{ $grade->name }}">{{ $grade->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
                                <button type="button" class="btn btn-light w-100" id="clear-filters">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <div class="d-flex gap-2 justify-content-lg-end flex-wrap">
                        @can('manage-sponsors')
                            @if (!session('is_past_term'))
                                <a href="{{ route('sponsors.sponsor-new') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>New Parent
                                </a>
                            @endif
                        @endcan
                        <div class="btn-group reports-dropdown">
                            <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2" style="font-size: 10px;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('sponsors.analysis-list') }}">
                                    <i class="fas fa-users me-2" style="color: #4287f5;"></i>Parents Report
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('sponsors.sponsors-students-list') }}">
                                    <i class="fas fa-child me-2" style="color: #6a5acd;"></i>Parents Children Report
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('sponsors.sponsors-contact-details') }}">
                                    <i class="fas fa-address-book me-2" style="color: #4287f5;"></i>Contact List
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('sponsors.import-list-report') }}">
                                    <i class="fas fa-file-import me-2" style="color: #6a5acd;"></i>Import List
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div id="sponsorsList">
                @include('sponsors.partials.sponsors-list', ['sponsors' => $sponsors])
            </div>
        </div>
    </div>
    @if (($communicationChannels['sms_enabled'] ?? false))
    <div class="modal fade" id="sendSMSModalSponsor" tabindex="-1" aria-labelledby="sendSMSModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendSMSModalLabel">
                        <i class="bx bx-message-rounded-dots me-2"></i>Send SMS Message
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Message</label>
                        <textarea id="smsText" name="message" class="form-control" rows="5"
                            placeholder="Type your SMS message here..."></textarea>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted">Characters: <span id="charCount">0</span></small>
                            <small class="text-muted fw-bold">SMS Count: <span id="smsCount">0</span></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary btn-loading" id="sendSMSButton">
                        <span class="btn-text"><i class="fas fa-paper-plane me-2"></i>Send SMS</span>
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
    <!-- Email Modal -->
    <div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendEmailModalLabel">
                        <i class="bx bx-mail-send me-2"></i>Compose Email
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="sendEmailForm" method="POST" action="{{ route('notifications.send-email') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="receiver-id" name="receiver_id">
                        <input type="hidden" id="receiver-type" name="receiver_type">
                        <div class="mb-3">
                            <label for="recipient-email" class="form-label fw-bold">To</label>
                            <input type="email" class="form-control" id="recipient-email"
                                name="recipient_email" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="email-subject" class="form-label fw-bold">Subject</label>
                            <input type="text" placeholder="Enter Subject ..." class="form-control"
                                id="email-subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="email-body" class="form-label fw-bold">Message</label>
                            <textarea placeholder="Email body here ..." class="form-control" cols="10" rows="10" id="email-body"
                                name="body"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Attachment <small class="text-muted">(Max 8MB)</small></label>
                            <div class="custom-file-input">
                                <input type="file" id="email-attachment" name="attachment">
                                <label for="email-attachment" class="file-input-label">
                                    <div class="file-input-icon">
                                        <i class="fas fa-paperclip"></i>
                                    </div>
                                    <div class="file-input-text">
                                        <span class="file-label">Choose File</span>
                                        <span class="file-hint" id="attachmentHint">PDF, DOC, XLS, or image files</span>
                                        <span class="file-selected d-none" id="attachmentName"></span>
                                    </div>
                                </label>
                            </div>
                            <small id="attachment-error" class="text-danger" style="display: none;">File size exceeds 8MB limit.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-loading" id="send-email-btn">
                            <span class="btn-text"><i class="fas fa-paper-plane me-2"></i>Send Email</span>
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
@endsection
@section('script')
    <script>
        $(document).ready(function() {

            function loadFiltersFromLocalStorage() {
                let savedSearch = localStorage.getItem('filter_search_sponsors');
                let savedStatus = localStorage.getItem('filter_status_sponsors');
                let savedSponsor = localStorage.getItem('filter_sponsor_sponsors');
                let savedGrade = localStorage.getItem('filter_grade_sponsors');

                if (savedSearch !== null) {
                    $('#name-search').val(savedSearch);
                }
                if (savedStatus !== null) {
                    $('#status-filter').val(savedStatus);
                }
                if (savedSponsor !== null) {
                    $('#sponsor-filter').val(savedSponsor);
                }
                if (savedGrade !== null) {
                    $('#grade-filter').val(savedGrade);
                }

                localStorage.removeItem('filter_gender_sponsors');
            }

            function saveFiltersToLocalStorage() {
                localStorage.setItem('filter_search_sponsors', $('#name-search').val());
                localStorage.setItem('filter_status_sponsors', $('#status-filter').val());
                localStorage.setItem('filter_sponsor_sponsors', $('#sponsor-filter').val());
                localStorage.setItem('filter_grade_sponsors', $('#grade-filter').val());
            }

            function fetchSponsorsData() {
                let searchVal = $('#name-search').val();
                let statusVal = $('#status-filter').val();
                let sponsorVal = $('#sponsor-filter').val();
                let gradeVal = $('#grade-filter').val();

                // Fade out existing content for smooth transition
                $('#sponsorsList').css('opacity', '0.5');

                $.ajax({
                    url: "{{ route('sponsors.index') }}",
                    method: 'GET',
                    data: {
                        search: searchVal,
                        status: statusVal,
                        sponsor: sponsorVal,
                        grade: gradeVal
                    },
                    success: function(response) {
                        if ($.fn.DataTable.isDataTable('#parents-table')) {
                            $('#parents-table').DataTable().destroy();
                        }
                        $('#sponsorsList').html(response);
                        if ($('#parents-table').length && !$.fn.DataTable.isDataTable('#parents-table')) {
                            $('#parents-table').DataTable({
                                searching: true,
                                dom: 'rtip',
                                lengthChange: false,
                                pageLength: 10,
                                language: {
                                    paginate: {
                                        previous: "<i class='mdi mdi-chevron-left'></i>",
                                        next: "<i class='mdi mdi-chevron-right'></i>"
                                    }
                                },
                                drawCallback: function() {
                                    $('.dataTables_paginate > .pagination').addClass(
                                        'pagination-rounded');
                                }
                            });

                            if (searchVal) {
                                $('#parents-table').DataTable().search(searchVal).draw();
                            }
                        }
                        $('#sponsorsList').css('opacity', '1');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching sponsors data:', error);
                        $('#sponsorsList').html(`
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="bx bxs-error-alt me-2 fs-4"></i>
                                <div>
                                    <strong>Oops! Something went wrong.</strong><br>
                                    We couldn't load the sponsors list. Please check your internet connection and try reloading the page. If the problem persists, please contact support.
                                </div>
                            </div>
                        `);
                        $('#sponsorsList').css('opacity', '1');
                    }
                });
            }

            $('#status-filter, #sponsor-filter, #grade-filter').change(function() {
                saveFiltersToLocalStorage();
                fetchSponsorsData();
            });

            // Name search with debounce
            let searchTimeout;
            $('#name-search').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    saveFiltersToLocalStorage();
                    // If DataTable exists, use its search
                    if ($.fn.DataTable.isDataTable('#parents-table')) {
                        $('#parents-table').DataTable().search($('#name-search').val()).draw();
                    }
                }, 300);
            });

            $('#clear-filters').click(function() {
                $('#name-search').val('');
                $('#status-filter').val('Current');
                $('#sponsor-filter').val('');
                $('#grade-filter').val('');
                localStorage.removeItem('filter_search_sponsors');
                localStorage.removeItem('filter_status_sponsors');
                localStorage.removeItem('filter_sponsor_sponsors');
                localStorage.removeItem('filter_grade_sponsors');
                fetchSponsorsData();
            });

            loadFiltersFromLocalStorage();

            // Check if any saved filters differ from defaults — if so, fetch filtered data
            let hasActiveFilters = $('#status-filter').val() && $('#status-filter').val() !== 'Current' ||
                $('#sponsor-filter').val() ||
                $('#grade-filter').val();

            if (hasActiveFilters) {
                fetchSponsorsData();
            } else {
                // Safe initial DataTable initialization (prevents reinitialization error)
                if ($('#parents-table').length && !$.fn.DataTable.isDataTable('#parents-table')) {
                    $('#parents-table').DataTable({
                        searching: true,
                        dom: 'rtip',
                        lengthChange: false,
                        pageLength: 10,
                        language: {
                            paginate: {
                                previous: "<i class='mdi mdi-chevron-left'></i>",
                                next: "<i class='mdi mdi-chevron-right'></i>"
                            }
                        },
                        drawCallback: function() {
                            $('.dataTables_paginate > .pagination')
                                .addClass('pagination-rounded');
                        }
                    });

                    // Apply saved search term if exists
                    let savedSearch = localStorage.getItem('filter_search_sponsors');
                    if (savedSearch) {
                        $('#parents-table').DataTable().search(savedSearch).draw();
                    }
                }
            }
        });

        function confirmDelete() {
            return confirm('Confirm if this parent is not associated with a student first, This action cannot be undone.');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const smsText = document.getElementById('smsText');
            const charCountEl = document.getElementById('charCount');
            const smsCountEl = document.getElementById('smsCount');
            const sendSMSButton = document.getElementById('sendSMSButton');

            let recipientId;
            let recipientType;

            $('#sendSMSModalSponsor').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                recipientId = button.data('recipient-id');
                recipientType = button.data('recipient-type');
            });

            if (sendSMSButton && smsText) {
                // Reset SMS button and form when modal is hidden
                $('#sendSMSModalSponsor').on('hidden.bs.modal', function() {
                    sendSMSButton.classList.remove('loading');
                    sendSMSButton.disabled = false;
                    smsText.value = '';
                    charCountEl.textContent = '0';
                    smsCountEl.textContent = '0';
                });

                smsText.addEventListener('input', function() {
                    const text = smsText.value;
                    const length = text.length;
                    const smsCount = Math.ceil(length / 160) || 0;

                    charCountEl.textContent = length;
                    smsCountEl.textContent = smsCount;
                });

                sendSMSButton.addEventListener('click', function() {
                    const message = smsText.value;
                    if (message.trim() === '') {
                        alert('Please enter a message.');
                        return;
                    }

                    sendSMSButton.classList.add('loading');
                    sendSMSButton.disabled = true;

                    var sendSmsUrlTemplate =
                        "{{ route('send.sms', ['recipientType' => ':recipientType', 'id' => ':id']) }}";
                    const url = sendSmsUrlTemplate.replace(':recipientType', recipientType).replace(':id',
                        recipientId);

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                message: message
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            sendSMSButton.classList.remove('loading');
                            sendSMSButton.disabled = false;

                            if (data.success) {
                                $('#sendSMSModalSponsor').modal('hide');
                                alert('SMS sent successfully');
                            } else {
                                alert('Error sending SMS: ' + data.message);
                            }
                        })
                        .catch(error => {
                            sendSMSButton.classList.remove('loading');
                            sendSMSButton.disabled = false;
                            alert('Error sending SMS. Please try again.');
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
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const attachmentInput = document.getElementById('email-attachment');
            const attachmentError = document.getElementById('attachment-error');
            const attachmentHint = document.getElementById('attachmentHint');
            const attachmentName = document.getElementById('attachmentName');
            const sendButton = document.getElementById('send-email-btn');
            const maxSize = 8 * 1024 * 1024;

            attachmentInput.addEventListener('change', function() {
                if (this.files[0]) {
                    const file = this.files[0];
                    const fileSize = file.size;
                    const fileSizeMB = fileSize / 1024 / 1024;

                    // Update file name display
                    attachmentHint.classList.add('d-none');
                    attachmentName.classList.remove('d-none');
                    attachmentName.textContent = `${file.name} (${fileSizeMB.toFixed(2)} MB)`;

                    if (fileSize > maxSize) {
                        attachmentError.style.display = 'block';
                        attachmentError.textContent = 'File size exceeds 8MB limit. Please choose a smaller file.';
                        sendButton.disabled = true;
                    } else {
                        attachmentError.style.display = 'none';
                        sendButton.disabled = false;
                    }
                } else {
                    // Reset to default state
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
                    alert('Attachment size exceeds 8MB limit. Please choose a smaller file.');
                }
            });

            // Reset file input when modal is closed
            $('#sendEmailModal').on('hidden.bs.modal', function() {
                attachmentHint.classList.remove('d-none');
                attachmentName.classList.add('d-none');
                attachmentName.textContent = '';
                attachmentError.style.display = 'none';
            });
        });
    </script>
@endsection

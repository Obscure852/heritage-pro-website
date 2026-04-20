@extends('layouts.master')
@section('title')
    Circulation
@endsection
@section('css')
    <style>
        .library-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .library-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .library-body {
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

        .btn-outline-secondary {
            background: transparent;
            border: 1px solid #d1d5db;
            color: #6b7280;
        }

        .btn-outline-secondary:hover {
            background: #f9fafb;
            border-color: #9ca3af;
            color: #374151;
        }

        .btn-outline-primary {
            background: transparent;
            border: 1px solid #3b82f6;
            color: #3b82f6;
        }

        .btn-outline-primary:hover {
            background: #eff6ff;
            color: #2563eb;
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

        /* Copy Info Card */
        .copy-info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #4e73df;
            border-radius: 0 3px 3px 0;
            padding: 16px;
            margin-bottom: 16px;
        }

        .copy-info-card .copy-title {
            font-weight: 600;
            color: #1f2937;
            font-size: 15px;
            margin-bottom: 4px;
        }

        .copy-info-card .copy-detail {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .copy-info-card .copy-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 6px;
        }

        .copy-status.status-available {
            background: #d1fae5;
            color: #065f46;
        }

        .copy-status.status-unavailable {
            background: #fee2e2;
            color: #991b1b;
        }

        .copy-info-card .copy-warning {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 3px;
            padding: 8px 12px;
            margin-top: 8px;
            font-size: 13px;
            color: #92400e;
        }

        .copy-info-card .copy-checked-out {
            background: #fff7ed;
            border: 1px solid #fb923c;
            border-radius: 3px;
            padding: 8px 12px;
            margin-top: 8px;
            font-size: 13px;
            color: #9a3412;
        }

        /* Borrower Info Card */
        .borrower-info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #36b9cc;
            border-radius: 0 3px 3px 0;
            padding: 16px;
            margin-bottom: 16px;
        }

        .borrower-info-card .borrower-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 15px;
            margin-bottom: 4px;
        }

        .borrower-info-card .borrower-detail {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .borrower-info-card .capacity-bar {
            background: #e5e7eb;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        .borrower-info-card .capacity-bar .capacity-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .capacity-fill.capacity-ok {
            background: #10b981;
        }

        .capacity-fill.capacity-warning {
            background: #f59e0b;
        }

        .capacity-fill.capacity-full {
            background: #ef4444;
        }

        /* Block Reason */
        .block-reason {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 3px;
            padding: 8px 12px;
            margin-bottom: 8px;
            font-size: 13px;
            color: #991b1b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .block-reason i {
            color: #dc2626;
            flex-shrink: 0;
        }

        /* Borrower Choices.js Override */
        .borrower-choices .choices__inner {
            min-height: 38px;
            border-radius: 3px;
        }

        /* Loan Items */
        .loan-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .loan-item .loan-info {
            flex: 1;
            min-width: 0;
        }

        .loan-item .loan-title {
            font-weight: 500;
            color: #1f2937;
        }

        .loan-item .loan-details {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        .loan-item .badge-overdue {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            margin-left: 8px;
        }

        .loan-item .btn-renew {
            background: transparent;
            border: 1px solid #3b82f6;
            color: #3b82f6;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
            margin-left: 8px;
        }

        .loan-item .btn-renew:hover {
            background: #eff6ff;
            color: #2563eb;
        }

        /* Scanned List */
        .scanned-list {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            max-height: 300px;
            overflow-y: auto;
        }

        .scanned-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
        }

        .scanned-item:last-child {
            border-bottom: none;
        }

        .scanned-item .scanned-info {
            flex: 1;
            min-width: 0;
        }

        .scanned-item .scanned-accession {
            font-weight: 600;
            color: #374151;
            font-family: 'Courier New', monospace;
        }

        .scanned-item .scanned-title {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        .scanned-item .btn-remove {
            background: transparent;
            border: none;
            color: #ef4444;
            font-size: 16px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 3px;
            transition: all 0.15s ease;
            flex-shrink: 0;
            margin-left: 8px;
        }

        .scanned-item .btn-remove:hover {
            background: #fef2f2;
        }

        .scanned-counter {
            font-size: 13px;
            color: #6b7280;
            margin-top: 8px;
        }

        /* Info/Warning boxes */
        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            padding: 10px 14px;
            font-size: 13px;
            color: #1e40af;
            margin-bottom: 12px;
        }

        .warning-box {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 3px;
            padding: 10px 14px;
            font-size: 13px;
            color: #92400e;
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        @media (max-width: 768px) {
            .library-header {
                padding: 20px;
            }

            .library-body {
                padding: 16px;
            }

            .nav-tabs-custom .nav-link {
                padding: 10px 12px;
                font-size: 13px;
            }

            .loan-item {
                flex-wrap: wrap;
                gap: 8px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('library.dashboard') }}">Library</a>
        @endslot
        @slot('title')
            Circulation
        @endslot
    @endcomponent

    <div class="library-container">
        <div class="library-header">
            <h4 class="mb-1 text-white"><i class="bx bx-transfer me-2"></i>Circulation Desk</h4>
            <p class="mb-0 opacity-75">Check out, check in, and manage book loans</p>
        </div>
        <div class="library-body">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start flex-wrap" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#checkout-tab" role="tab">
                                <i class="bx bx-log-out-circle me-2 text-muted"></i>Check Out
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#checkin-tab" role="tab">
                                <i class="bx bx-log-in-circle me-2 text-muted"></i>Check In
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#bulk-checkout-tab" role="tab">
                                <i class="bx bx-transfer-alt me-2 text-muted"></i>Bulk Check Out
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#bulk-checkin-tab" role="tab">
                                <i class="bx bx-transfer-alt me-2 text-muted"></i>Bulk Check In
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        @include('library.circulation._checkout-tab')
                        @include('library.circulation._checkin-tab')
                        @include('library.circulation._bulk-checkout')
                        @include('library.circulation._bulk-checkin')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('library.circulation._renewal-modal')
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/onscan.js@1.5.1/onscan.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ====================================
            // CSRF Token Setup for jQuery AJAX
            // ====================================
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ====================================
            // Tab Persistence via localStorage
            // ====================================
            const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
            tabLinks.forEach(function(tabLink) {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    var activeTabHref = event.target.getAttribute('href');
                    localStorage.setItem('circulationActiveTab', activeTabHref);
                });
            });

            var savedTab = localStorage.getItem('circulationActiveTab');
            if (savedTab) {
                var tabTriggerEl = document.querySelector('.nav-link[href="' + savedTab + '"]');
                if (tabTriggerEl) {
                    var tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                }
            }

            // ====================================
            // onScan.js Barcode Scanner Detection
            // ====================================
            if (typeof onScan !== 'undefined') {
                onScan.attachTo(document, {
                    avgTimeByChar: 30,
                    suffixKeyCodes: [13],
                    onScan: function(sCode) {
                        var activeTab = document.querySelector('.tab-pane.active');
                        if (!activeTab) return;

                        var targetInput = null;
                        switch (activeTab.id) {
                            case 'checkout-tab':
                                targetInput = document.getElementById('checkout-accession');
                                break;
                            case 'checkin-tab':
                                targetInput = document.getElementById('checkin-accession');
                                break;
                            case 'bulk-checkout-tab':
                                targetInput = document.getElementById('bulk-checkout-accession');
                                break;
                            case 'bulk-checkin-tab':
                                targetInput = document.getElementById('bulk-checkin-accession');
                                break;
                        }

                        if (targetInput) {
                            targetInput.value = sCode;
                            targetInput.focus();
                            targetInput.dispatchEvent(new Event('input', { bubbles: true }));
                            // Trigger the lookup/add action
                            var lookupBtn = targetInput.closest('.input-group').querySelector('.btn-lookup');
                            if (lookupBtn) {
                                lookupBtn.click();
                            }
                        }
                    }
                });
            }

            // ====================================
            // Utility Functions
            // ====================================
            function escapeHtml(text) {
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(text));
                return div.innerHTML;
            }

            function setButtonLoading(btn, loading) {
                if (loading) {
                    btn.classList.add('loading');
                    btn.disabled = true;
                } else {
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
            }

            function resetForm(prefix) {
                $('#' + prefix + '-accession').val('');
                $('#' + prefix + '-copy-info').hide().html('');
                if (prefix === 'checkout' || prefix === 'bulk-checkout') {
                    var choicesRef = window[prefix + 'BorrowerChoices'];
                    if (choicesRef) {
                        choicesRef.removeActiveItems();
                        choicesRef.clearChoices();
                    }
                    $('#' + prefix + '-borrower-status').hide().html('');
                    $('#' + prefix + '-borrower-type').val('');
                    $('#' + prefix + '-borrower-id').val('');
                }
                if (prefix === 'checkout') {
                    $('#checkout-notes').val('');
                    $('#checkout-submit-btn').prop('disabled', true);
                    window.checkoutCopyReady = false;
                    window.checkoutBorrowerReady = false;
                }
                if (prefix === 'checkin') {
                    $('#checkin-notes').val('');
                    $('#checkin-submit-btn').prop('disabled', true);
                    window.checkinReady = false;
                }
            }

            // ====================================
            // Borrower Choices.js Initialization
            // ====================================
            function initBorrowerChoices(selector, prefix) {
                var element = document.querySelector(selector);
                var choicesInstance = new Choices(element, {
                    searchEnabled: true,
                    placeholder: true,
                    placeholderValue: 'Search borrower by name or ID...',
                    searchPlaceholderValue: 'Type to search...',
                    noResultsText: 'No borrowers found',
                    noChoicesText: 'Type at least 2 characters to search...',
                    shouldSort: false,
                    itemSelectText: '',
                    searchFloor: 1,
                    searchResultLimit: 20,
                    classNames: {
                        containerOuter: 'choices'
                    }
                });

                // Store reference for reset and borrower name retrieval
                window[prefix + 'BorrowerChoices'] = choicesInstance;

                // AJAX search on typing in the Choices.js search input
                var searchTimer = null;
                choicesInstance.input.element.addEventListener('input', function() {
                    var query = this.value.trim();
                    clearTimeout(searchTimer);

                    if (query.length < 2) return;

                    searchTimer = setTimeout(function() {
                        $.ajax({
                            url: '{{ route("library.borrowers.search") }}',
                            method: 'GET',
                            data: { search: query },
                            success: function(results) {
                                var choices = results.map(function(item) {
                                    var morphType = item.borrower_type === 'student' ? 'student' : 'user';
                                    var typeLabel = item.type === 'student' ? 'Student' : 'Staff';
                                    return {
                                        value: morphType + ':' + item.id,
                                        label: item.name + ' \u2014 ' + typeLabel + ' (' + item.identifier + ')',
                                        selected: false,
                                        disabled: false
                                    };
                                });
                                choicesInstance.setChoices(choices, 'value', 'label', true);
                            }
                        });
                    }, 300);
                });

                // Handle selection
                element.addEventListener('change', function() {
                    var value = this.value;
                    if (!value) {
                        $('#' + prefix + '-borrower-type').val('');
                        $('#' + prefix + '-borrower-id').val('');
                        $('#' + prefix + '-borrower-status').hide().html('');
                        if (prefix === 'checkout') {
                            window.checkoutBorrowerReady = false;
                            updateCheckoutButton();
                        } else if (prefix === 'bulk-checkout') {
                            window.bulkCheckoutBorrowerReady = false;
                            window.bulkCheckoutItems = [];
                            renderBulkCheckoutList();
                            updateBulkCheckoutState();
                        }
                        return;
                    }

                    var parts = value.split(':');
                    var morphType = parts[0];
                    var borrowerId = parts[1];

                    $('#' + prefix + '-borrower-type').val(morphType);
                    $('#' + prefix + '-borrower-id').val(borrowerId);

                    if (prefix === 'bulk-checkout') {
                        window.bulkCheckoutItems = [];
                        renderBulkCheckoutList();
                    }

                    fetchBorrowerStatus(prefix, morphType, borrowerId);
                });
            }

            initBorrowerChoices('#checkout-borrower-select', 'checkout');
            initBorrowerChoices('#bulk-checkout-borrower-select', 'bulk-checkout');

            // ====================================
            // CHECKOUT TAB
            // ====================================
            window.checkoutCopyReady = false;
            window.checkoutBorrowerReady = false;
            window.checkoutAccessionNumber = '';

            function updateCheckoutButton() {
                var enabled = window.checkoutCopyReady && window.checkoutBorrowerReady;
                $('#checkout-submit-btn').prop('disabled', !enabled);
            }

            // Accession lookup
            function checkoutLookup() {
                var accession = $('#checkout-accession').val().trim();
                if (!accession) return;

                window.checkoutCopyReady = false;
                window.checkoutAccessionNumber = accession;
                updateCheckoutButton();

                $.ajax({
                    url: '{{ route("library.circulation.lookup-copy") }}',
                    method: 'GET',
                    data: { accession_number: accession },
                    success: function(response) {
                        if (response.success) {
                            var copy = response.data.copy;
                            var txn = response.data.active_transaction;
                            var statusClass = copy.status === 'available' ? 'status-available' : 'status-unavailable';
                            var statusLabel = copy.status.charAt(0).toUpperCase() + copy.status.slice(1);

                            var html = '<div class="copy-info-card">';
                            html += '<div class="copy-title">' + escapeHtml(copy.book_title) + '</div>';
                            if (copy.book_authors) {
                                html += '<div class="copy-detail"><i class="bx bx-user me-1"></i>' + escapeHtml(copy.book_authors) + '</div>';
                            }
                            if (copy.book_isbn) {
                                html += '<div class="copy-detail"><i class="bx bx-barcode me-1"></i>ISBN: ' + escapeHtml(copy.book_isbn) + '</div>';
                            }
                            html += '<div class="copy-detail"><i class="bx bx-hash me-1"></i>Accession: ' + escapeHtml(copy.accession_number) + '</div>';
                            html += '<span class="copy-status ' + statusClass + '">' + escapeHtml(statusLabel) + '</span>';

                            if (copy.status !== 'available') {
                                html += '<div class="copy-warning"><i class="bx bx-error me-1"></i>This copy is not available for checkout (status: ' + escapeHtml(copy.status) + ')</div>';
                            }

                            if (txn) {
                                html += '<div class="copy-checked-out"><i class="bx bx-info-circle me-1"></i>Currently checked out to <strong>' + escapeHtml(txn.borrower_name) + '</strong> since ' + escapeHtml(txn.checkout_date) + ', due ' + escapeHtml(txn.due_date) + '</div>';
                            }

                            html += '</div>';
                            $('#checkout-copy-info').html(html).show();

                            if (copy.status === 'available' && !txn) {
                                window.checkoutCopyReady = true;
                            }
                            updateCheckoutButton();
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Copy not found.';
                        $('#checkout-copy-info').html('<div class="warning-box"><i class="bx bx-error me-1"></i>' + escapeHtml(msg) + '</div>').show();
                        window.checkoutCopyReady = false;
                        updateCheckoutButton();
                    }
                });
            }

            $('#checkout-lookup-btn').on('click', function() {
                checkoutLookup();
            });

            $('#checkout-accession').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    checkoutLookup();
                }
            });

            function fetchBorrowerStatus(prefix, borrowerType, borrowerId) {
                $.ajax({
                    url: '{{ route("library.circulation.borrower-status") }}',
                    method: 'GET',
                    data: { borrower_type: borrowerType, borrower_id: borrowerId },
                    success: function(response) {
                        if (response.success) {
                            renderBorrowerStatus(prefix, response.data);
                        }
                    },
                    error: function() {
                        $('#' + prefix + '-borrower-status').html('<div class="warning-box">Failed to load borrower status.</div>').show();
                    }
                });
            }

            function renderBorrowerStatus(prefix, data) {
                var html = '<div class="borrower-info-card">';

                // Capacity bar
                var percentage = data.max_books > 0 ? Math.round((data.current_loans_count / data.max_books) * 100) : 0;
                var capacityClass = percentage >= 100 ? 'capacity-full' : (percentage >= 70 ? 'capacity-warning' : 'capacity-ok');

                html += '<div class="borrower-detail"><strong>' + data.current_loans_count + '/' + data.max_books + '</strong> books borrowed</div>';
                html += '<div class="capacity-bar"><div class="capacity-fill ' + capacityClass + '" style="width: ' + Math.min(percentage, 100) + '%"></div></div>';

                html += '</div>';

                // Block reasons
                if (data.block_reasons && data.block_reasons.length > 0) {
                    data.block_reasons.forEach(function(reason) {
                        html += '<div class="block-reason"><i class="bx bx-block"></i>' + escapeHtml(reason) + '</div>';
                    });
                }

                // Current loans
                if (data.current_loans && data.current_loans.length > 0) {
                    html += '<div class="mt-3"><div class="section-title" style="font-size:14px;">Current Loans</div>';
                    data.current_loans.forEach(function(loan) {
                        html += '<div class="loan-item">';
                        html += '<div class="loan-info">';
                        html += '<div class="loan-title">' + escapeHtml(loan.book_title) + '</div>';
                        html += '<div class="loan-details">Accession: ' + escapeHtml(loan.accession_number) + ' &middot; Due: ' + escapeHtml(loan.due_date);
                        if (loan.is_overdue) {
                            html += ' <span class="badge-overdue">Overdue</span>';
                        }
                        if (loan.renewal_count > 0) {
                            html += ' &middot; Renewed ' + loan.renewal_count + 'x';
                        }
                        html += '</div>';
                        html += '</div>';
                        html += '<button type="button" class="btn-renew" data-transaction-id="' + loan.id + '" data-book-title="' + escapeHtml(loan.book_title) + '" data-due-date="' + escapeHtml(loan.due_date) + '" data-renewal-count="' + loan.renewal_count + '"><i class="bx bx-refresh me-1"></i>Renew</button>';
                        html += '</div>';
                    });
                    html += '</div>';
                }

                $('#' + prefix + '-borrower-status').html(html).show();

                // Update borrow readiness
                if (prefix === 'checkout') {
                    window.checkoutBorrowerReady = data.can_borrow;
                    updateCheckoutButton();
                } else if (prefix === 'bulk-checkout') {
                    window.bulkCheckoutBorrowerReady = data.can_borrow;
                    window.bulkCheckoutRemainingCapacity = data.max_books - data.current_loans_count;
                    updateBulkCheckoutState();
                }
            }

            // Checkout submit
            $('#checkout-submit-btn').on('click', function() {
                var btn = this;
                setButtonLoading(btn, true);

                $.ajax({
                    url: '{{ route("library.circulation.checkout") }}',
                    method: 'POST',
                    data: {
                        accession_number: window.checkoutAccessionNumber,
                        borrower_type: $('#checkout-borrower-type').val(),
                        borrower_id: $('#checkout-borrower-id').val(),
                        notes: $('#checkout-notes').val()
                    },
                    success: function(response) {
                        setButtonLoading(btn, false);
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Book Checked Out',
                                html: '<strong>' + escapeHtml(response.data.book_title) + '</strong><br>Due date: ' + escapeHtml(response.data.due_date)
                            });
                            resetForm('checkout');
                            // Refresh borrower status if borrower is still selected
                            var bType = $('#checkout-borrower-type').val();
                            var bId = $('#checkout-borrower-id').val();
                            if (bType && bId) {
                                fetchBorrowerStatus('checkout', bType, bId);
                            }
                        }
                    },
                    error: function(xhr) {
                        setButtonLoading(btn, false);
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Checkout Failed',
                            text: msg
                        });
                    }
                });
            });

            // ====================================
            // CHECKIN TAB
            // ====================================
            window.checkinReady = false;
            window.checkinAccessionNumber = '';

            function checkinLookup() {
                var accession = $('#checkin-accession').val().trim();
                if (!accession) return;

                window.checkinReady = false;
                window.checkinAccessionNumber = accession;
                $('#checkin-submit-btn').prop('disabled', true);

                $.ajax({
                    url: '{{ route("library.circulation.lookup-copy") }}',
                    method: 'GET',
                    data: { accession_number: accession },
                    success: function(response) {
                        if (response.success) {
                            var copy = response.data.copy;
                            var txn = response.data.active_transaction;

                            if (!txn) {
                                $('#checkin-copy-info').html('<div class="info-box"><i class="bx bx-info-circle me-1"></i>This book is not currently checked out.</div>').show();
                                window.checkinReady = false;
                                $('#checkin-submit-btn').prop('disabled', true);
                                return;
                            }

                            var html = '<div class="copy-info-card">';
                            html += '<div class="copy-title">' + escapeHtml(copy.book_title) + '</div>';
                            if (copy.book_authors) {
                                html += '<div class="copy-detail"><i class="bx bx-user me-1"></i>' + escapeHtml(copy.book_authors) + '</div>';
                            }
                            html += '<div class="copy-detail"><i class="bx bx-hash me-1"></i>Accession: ' + escapeHtml(copy.accession_number) + '</div>';
                            html += '<div class="copy-detail"><i class="bx bx-user-circle me-1"></i>Borrower: <strong>' + escapeHtml(txn.borrower_name) + '</strong></div>';
                            html += '<div class="copy-detail"><i class="bx bx-calendar me-1"></i>Checked out: ' + escapeHtml(txn.checkout_date) + ' &middot; Due: ' + escapeHtml(txn.due_date) + '</div>';

                            if (txn.is_overdue) {
                                html += '<div class="copy-warning"><i class="bx bx-error me-1"></i>This book is <strong>overdue</strong></div>';
                            }

                            if (txn.renewal_count > 0) {
                                html += '<div class="copy-detail"><i class="bx bx-refresh me-1"></i>Renewed ' + txn.renewal_count + ' time(s)</div>';
                            }

                            html += '</div>';
                            $('#checkin-copy-info').html(html).show();

                            window.checkinReady = true;
                            $('#checkin-submit-btn').prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Copy not found.';
                        $('#checkin-copy-info').html('<div class="warning-box"><i class="bx bx-error me-1"></i>' + escapeHtml(msg) + '</div>').show();
                        window.checkinReady = false;
                        $('#checkin-submit-btn').prop('disabled', true);
                    }
                });
            }

            $('#checkin-lookup-btn').on('click', function() {
                checkinLookup();
            });

            $('#checkin-accession').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    checkinLookup();
                }
            });

            // Checkin submit
            $('#checkin-submit-btn').on('click', function() {
                var btn = this;
                setButtonLoading(btn, true);

                $.ajax({
                    url: '{{ route("library.circulation.checkin") }}',
                    method: 'POST',
                    data: {
                        accession_number: window.checkinAccessionNumber,
                        notes: $('#checkin-notes').val()
                    },
                    success: function(response) {
                        setButtonLoading(btn, false);
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Book Returned',
                                html: '<strong>' + escapeHtml(response.data.book_title) + '</strong><br>Returned by: ' + escapeHtml(response.data.borrower_name)
                            });
                            resetForm('checkin');
                        }
                    },
                    error: function(xhr) {
                        setButtonLoading(btn, false);
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Check-in Failed',
                            text: msg
                        });
                    }
                });
            });

            // ====================================
            // BULK CHECKOUT TAB
            // ====================================
            window.bulkCheckoutItems = [];
            window.bulkCheckoutBorrowerReady = false;
            window.bulkCheckoutRemainingCapacity = 0;

            function updateBulkCheckoutState() {
                var canAdd = window.bulkCheckoutBorrowerReady && $('#bulk-checkout-borrower-type').val();
                $('#bulk-checkout-accession').prop('disabled', !canAdd);
                $('#bulk-checkout-add-btn').prop('disabled', !canAdd);
                $('#bulk-checkout-process-btn').prop('disabled', !(canAdd && window.bulkCheckoutItems.length > 0));
            }

            function renderBulkCheckoutList() {
                var $list = $('#bulk-checkout-list');
                if (window.bulkCheckoutItems.length === 0) {
                    $list.html('<div class="p-3 text-center text-muted">No books scanned yet</div>');
                    $('#bulk-checkout-counter').text('0 books queued');
                    updateBulkCheckoutState();
                    return;
                }

                var html = '';
                window.bulkCheckoutItems.forEach(function(item, index) {
                    html += '<div class="scanned-item">';
                    html += '<div class="scanned-info">';
                    html += '<div class="scanned-accession">' + escapeHtml(item.accession_number) + '</div>';
                    html += '<div class="scanned-title">' + escapeHtml(item.book_title) + '</div>';
                    html += '</div>';
                    html += '<button type="button" class="btn-remove" data-index="' + index + '" title="Remove"><i class="bx bx-x"></i></button>';
                    html += '</div>';
                });

                $list.html(html);
                $('#bulk-checkout-counter').text(window.bulkCheckoutItems.length + ' book(s) queued');
                updateBulkCheckoutState();
            }

            // Bulk checkout add book
            function bulkCheckoutAddBook() {
                var accession = $('#bulk-checkout-accession').val().trim();
                if (!accession) return;

                // Check for duplicates
                var isDuplicate = window.bulkCheckoutItems.some(function(item) {
                    return item.accession_number === accession;
                });

                if (isDuplicate) {
                    Swal.fire({ icon: 'warning', title: 'Duplicate', text: 'This accession number is already in the list.' });
                    return;
                }

                // Check capacity
                if (window.bulkCheckoutItems.length >= window.bulkCheckoutRemainingCapacity) {
                    Swal.fire({ icon: 'warning', title: 'Capacity Exceeded', text: 'Adding this book would exceed the borrower\'s remaining capacity.' });
                    return;
                }

                $.ajax({
                    url: '{{ route("library.circulation.lookup-copy") }}',
                    method: 'GET',
                    data: { accession_number: accession },
                    success: function(response) {
                        if (response.success) {
                            var copy = response.data.copy;
                            if (copy.status !== 'available' || response.data.active_transaction) {
                                Swal.fire({ icon: 'warning', title: 'Not Available', text: 'Copy "' + copy.accession_number + '" is not available for checkout.' });
                                return;
                            }

                            window.bulkCheckoutItems.push({
                                accession_number: copy.accession_number,
                                book_title: copy.book_title
                            });
                            renderBulkCheckoutList();
                            $('#bulk-checkout-accession').val('').focus();
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Copy not found.';
                        Swal.fire({ icon: 'error', title: 'Not Found', text: msg });
                    }
                });
            }

            $('#bulk-checkout-add-btn').on('click', function() {
                bulkCheckoutAddBook();
            });

            $('#bulk-checkout-accession').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    bulkCheckoutAddBook();
                }
            });

            // Remove from bulk checkout list
            $(document).on('click', '#bulk-checkout-list .btn-remove', function() {
                var index = $(this).data('index');
                window.bulkCheckoutItems.splice(index, 1);
                renderBulkCheckoutList();
            });

            // Bulk checkout process
            $('#bulk-checkout-process-btn').on('click', function() {
                var btn = this;
                setButtonLoading(btn, true);

                var accessionNumbers = window.bulkCheckoutItems.map(function(item) {
                    return item.accession_number;
                });

                $.ajax({
                    url: '{{ route("library.circulation.bulk-checkout") }}',
                    method: 'POST',
                    data: {
                        accession_numbers: accessionNumbers,
                        borrower_type: $('#bulk-checkout-borrower-type').val(),
                        borrower_id: $('#bulk-checkout-borrower-id').val(),
                        notes: $('#bulk-checkout-notes').val()
                    },
                    success: function(response) {
                        setButtonLoading(btn, false);
                        var successHtml = '';
                        var errorHtml = '';

                        if (response.data.success && response.data.success.length > 0) {
                            successHtml = '<div style="text-align:left;margin-bottom:8px;"><strong style="color:#065f46;">Checked out:</strong></div>';
                            successHtml += '<ul style="text-align:left;list-style:none;padding:0;margin:0;">';
                            response.data.success.forEach(function(item) {
                                successHtml += '<li style="padding:4px 0;color:#065f46;"><i class="bx bx-check"></i> ' + escapeHtml(item.book_title) + ' (due ' + escapeHtml(item.due_date) + ')</li>';
                            });
                            successHtml += '</ul>';
                        }

                        if (response.data.errors && response.data.errors.length > 0) {
                            errorHtml = '<div style="text-align:left;margin-top:8px;"><strong style="color:#991b1b;">Errors:</strong></div>';
                            errorHtml += '<ul style="text-align:left;list-style:none;padding:0;margin:0;">';
                            response.data.errors.forEach(function(item) {
                                errorHtml += '<li style="padding:4px 0;color:#991b1b;"><i class="bx bx-x"></i> ' + escapeHtml(item.accession_number) + ': ' + escapeHtml(item.error) + '</li>';
                            });
                            errorHtml += '</ul>';
                        }

                        Swal.fire({
                            icon: response.data.errors && response.data.errors.length > 0 ? 'warning' : 'success',
                            title: response.message,
                            html: successHtml + errorHtml
                        });

                        // Clear the list
                        window.bulkCheckoutItems = [];
                        renderBulkCheckoutList();
                        $('#bulk-checkout-notes').val('');

                        // Refresh borrower status
                        var bType = $('#bulk-checkout-borrower-type').val();
                        var bId = $('#bulk-checkout-borrower-id').val();
                        if (bType && bId) {
                            fetchBorrowerStatus('bulk-checkout', bType, bId);
                        }
                    },
                    error: function(xhr) {
                        setButtonLoading(btn, false);
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred.';
                        Swal.fire({ icon: 'error', title: 'Bulk Checkout Failed', text: msg });
                    }
                });
            });

            // ====================================
            // BULK CHECKIN TAB
            // ====================================
            window.bulkCheckinItems = [];

            function renderBulkCheckinList() {
                var $list = $('#bulk-checkin-list');
                if (window.bulkCheckinItems.length === 0) {
                    $list.html('<div class="p-3 text-center text-muted">No books scanned yet</div>');
                    $('#bulk-checkin-counter').text('0 books to return');
                    $('#bulk-checkin-process-btn').prop('disabled', true);
                    return;
                }

                var html = '';
                window.bulkCheckinItems.forEach(function(item, index) {
                    html += '<div class="scanned-item">';
                    html += '<div class="scanned-info">';
                    html += '<div class="scanned-accession">' + escapeHtml(item.accession_number) + '</div>';
                    html += '<div class="scanned-title">' + escapeHtml(item.book_title) + ' &middot; ' + escapeHtml(item.borrower_name);
                    if (item.is_overdue) {
                        html += ' <span class="badge-overdue" style="display:inline-block;">Overdue</span>';
                    }
                    html += '</div>';
                    html += '</div>';
                    html += '<button type="button" class="btn-remove" data-index="' + index + '" title="Remove"><i class="bx bx-x"></i></button>';
                    html += '</div>';
                });

                $list.html(html);
                $('#bulk-checkin-counter').text(window.bulkCheckinItems.length + ' book(s) to return');
                $('#bulk-checkin-process-btn').prop('disabled', false);
            }

            // Bulk checkin add book
            function bulkCheckinAddBook() {
                var accession = $('#bulk-checkin-accession').val().trim();
                if (!accession) return;

                // Check for duplicates
                var isDuplicate = window.bulkCheckinItems.some(function(item) {
                    return item.accession_number === accession;
                });

                if (isDuplicate) {
                    Swal.fire({ icon: 'warning', title: 'Duplicate', text: 'This accession number is already in the list.' });
                    return;
                }

                $.ajax({
                    url: '{{ route("library.circulation.lookup-copy") }}',
                    method: 'GET',
                    data: { accession_number: accession },
                    success: function(response) {
                        if (response.success) {
                            var copy = response.data.copy;
                            var txn = response.data.active_transaction;

                            if (!txn) {
                                Swal.fire({ icon: 'warning', title: 'Not Checked Out', text: 'Book "' + copy.accession_number + '" is not currently checked out.' });
                                return;
                            }

                            window.bulkCheckinItems.push({
                                accession_number: copy.accession_number,
                                book_title: copy.book_title,
                                borrower_name: txn.borrower_name,
                                is_overdue: txn.is_overdue
                            });
                            renderBulkCheckinList();
                            $('#bulk-checkin-accession').val('').focus();
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Copy not found.';
                        Swal.fire({ icon: 'error', title: 'Not Found', text: msg });
                    }
                });
            }

            $('#bulk-checkin-add-btn').on('click', function() {
                bulkCheckinAddBook();
            });

            $('#bulk-checkin-accession').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    bulkCheckinAddBook();
                }
            });

            // Remove from bulk checkin list
            $(document).on('click', '#bulk-checkin-list .btn-remove', function() {
                var index = $(this).data('index');
                window.bulkCheckinItems.splice(index, 1);
                renderBulkCheckinList();
            });

            // Bulk checkin process
            $('#bulk-checkin-process-btn').on('click', function() {
                var btn = this;
                setButtonLoading(btn, true);

                var accessionNumbers = window.bulkCheckinItems.map(function(item) {
                    return item.accession_number;
                });

                $.ajax({
                    url: '{{ route("library.circulation.bulk-checkin") }}',
                    method: 'POST',
                    data: { accession_numbers: accessionNumbers },
                    success: function(response) {
                        setButtonLoading(btn, false);
                        var successHtml = '';
                        var errorHtml = '';

                        if (response.data.success && response.data.success.length > 0) {
                            successHtml = '<div style="text-align:left;margin-bottom:8px;"><strong style="color:#065f46;">Returned:</strong></div>';
                            successHtml += '<ul style="text-align:left;list-style:none;padding:0;margin:0;">';
                            response.data.success.forEach(function(item) {
                                successHtml += '<li style="padding:4px 0;color:#065f46;"><i class="bx bx-check"></i> ' + escapeHtml(item.book_title) + ' (' + escapeHtml(item.borrower_name) + ')</li>';
                            });
                            successHtml += '</ul>';
                        }

                        if (response.data.errors && response.data.errors.length > 0) {
                            errorHtml = '<div style="text-align:left;margin-top:8px;"><strong style="color:#991b1b;">Errors:</strong></div>';
                            errorHtml += '<ul style="text-align:left;list-style:none;padding:0;margin:0;">';
                            response.data.errors.forEach(function(item) {
                                errorHtml += '<li style="padding:4px 0;color:#991b1b;"><i class="bx bx-x"></i> ' + escapeHtml(item.accession_number) + ': ' + escapeHtml(item.error) + '</li>';
                            });
                            errorHtml += '</ul>';
                        }

                        Swal.fire({
                            icon: response.data.errors && response.data.errors.length > 0 ? 'warning' : 'success',
                            title: response.message,
                            html: successHtml + errorHtml
                        });

                        // Clear list
                        window.bulkCheckinItems = [];
                        renderBulkCheckinList();
                    },
                    error: function(xhr) {
                        setButtonLoading(btn, false);
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred.';
                        Swal.fire({ icon: 'error', title: 'Bulk Check-in Failed', text: msg });
                    }
                });
            });

            // ====================================
            // RENEWAL MODAL
            // ====================================
            $(document).on('click', '.btn-renew', function() {
                var txnId = $(this).data('transaction-id');
                var bookTitle = $(this).data('book-title');
                var dueDate = $(this).data('due-date');
                var renewalCount = $(this).data('renewal-count');
                var borrowerName = '';
                var checkoutChoice = window.checkoutBorrowerChoices ? window.checkoutBorrowerChoices.getValue() : null;
                var bulkChoice = window['bulk-checkoutBorrowerChoices'] ? window['bulk-checkoutBorrowerChoices'].getValue() : null;
                if (checkoutChoice && checkoutChoice.value) {
                    borrowerName = checkoutChoice.label.split(' \u2014 ')[0];
                } else if (bulkChoice && bulkChoice.value) {
                    borrowerName = bulkChoice.label.split(' \u2014 ')[0];
                }

                $('#renewal-transaction-id').val(txnId);
                $('#renewal-book-title').text(bookTitle);
                $('#renewal-borrower-name').text(borrowerName);
                $('#renewal-current-due-date').text(dueDate);
                $('#renewal-count').text(renewalCount);
                $('#renewal-notes').val('');

                var modal = new bootstrap.Modal(document.getElementById('renewal-modal'));
                modal.show();
            });

            $('#renewal-confirm-btn').on('click', function() {
                var btn = this;
                var txnId = $('#renewal-transaction-id').val();
                setButtonLoading(btn, true);

                var renewUrl = '{{ route("library.circulation.renew", ":id") }}'.replace(':id', txnId);

                $.ajax({
                    url: renewUrl,
                    method: 'POST',
                    data: {
                        notes: $('#renewal-notes').val()
                    },
                    success: function(response) {
                        setButtonLoading(btn, false);
                        var modal = bootstrap.Modal.getInstance(document.getElementById('renewal-modal'));
                        if (modal) modal.hide();

                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Loan Renewed',
                                html: 'New due date: <strong>' + escapeHtml(response.data.due_date) + '</strong><br>Renewal #' + response.data.renewal_count
                            });

                            // Refresh borrower status
                            var bType = $('#checkout-borrower-type').val() || $('#bulk-checkout-borrower-type').val();
                            var bId = $('#checkout-borrower-id').val() || $('#bulk-checkout-borrower-id').val();
                            if (bType && bId) {
                                var prefix = $('#checkout-borrower-type').val() ? 'checkout' : 'bulk-checkout';
                                fetchBorrowerStatus(prefix, bType, bId);
                            }
                        }
                    },
                    error: function(xhr) {
                        setButtonLoading(btn, false);
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred.';
                        Swal.fire({ icon: 'error', title: 'Renewal Failed', text: msg });
                    }
                });
            });

            // Init
            renderBulkCheckoutList();
            renderBulkCheckinList();
        });
    </script>
@endsection

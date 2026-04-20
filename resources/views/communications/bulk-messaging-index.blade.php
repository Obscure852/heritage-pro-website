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

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            padding: 8px 16px;
            font-weight: 500;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-outline-primary {
            border: 1px solid #3b82f6;
            color: #3b82f6;
            padding: 8px 16px;
            font-weight: 500;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-outline-primary:hover {
            background: #3b82f6;
            color: white;
            transform: translateY(-1px);
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

        .alert {
            border-radius: 3px;
            border: none;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('notifications.index') }}">Communications</a>
        @endslot
        @slot('title')
            Bulk Messaging
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-all me-2"></i>
            {{ session('message') }}
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
                            <h4>Bulk Messaging Dashboard</h4>
                            <p>Send and manage bulk communications to staff and parents/sponsors</p>
                        </div>
                        <div class="col-md-6">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h4 class="mb-0 fw-bold text-white" id="stat-sms">--</h4>
                                        <small class="opacity-75">SMS Sent</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h4 class="mb-0 fw-bold text-white" id="stat-emails">--</h4>
                                        <small class="opacity-75">Emails</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h4 class="mb-0 fw-bold text-white" id="stat-balance">--</h4>
                                        <small class="opacity-75">Balance</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="admissions-body">
                    <div class="help-text">
                        <i class="fas fa-info-circle me-2"></i>
                        View bulk messaging activity and statistics for the selected term. Use this dashboard to monitor message delivery and engagement.
                    </div>

                    <div class="d-flex justify-content-end gap-2 mb-4">
                        <a href="{{ route('notifications.sms-job-history') }}" class="btn btn-outline-primary">
                            <i class="fas fa-history me-1"></i> Job History
                        </a>
                        <a href="{{ route('notifications.bulk-mail-index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-1"></i> Send Email
                        </a>
                        <a href="{{ route('notifications.bulk-sms-index') }}" class="btn btn-primary">
                            <i class="fas fa-sms me-1"></i> Send SMS
                        </a>
                    </div>

                    <div id="bulk-messaging">
                        <!-- Content loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {

            $('#termId').change(function() {
                var term = $(this).val();
                var studentsSessionUrl = "{{ route('students.term-session') }}";

                // Show loading state
                $('#stat-sms').text('--');
                $('#stat-emails').text('--');
                $('#stat-balance').text('--');

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
                        fetchTermData();
                    }
                });
            });

            function fetchTermData() {
                // Fetch messages data
                var messagesUrl = "{{ route('notifications.get-messages') }}";
                $.ajax({
                    url: messagesUrl,
                    method: 'GET',
                    success: function(response) {
                        $('#bulk-messaging').html(response);

                        // Don't initialize DataTable - the view has its own custom filtering/pagination
                        // The messages-term.blade.php already handles filtering and pagination

                        // Update stats from the loaded content
                        updateStats();
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching messages:", xhr.status, xhr.statusText);
                        $('#bulk-messaging').html(`
                            <div class="alert alert-danger" role="alert">
                                <i class="mdi mdi-alert-circle-outline me-2"></i>
                                Failed to load messaging data. Please try again.
                            </div>
                        `);
                    }
                });

                // Fetch emails data for stats
                var emailsUrl = "{{ route('notifications.get-emails') }}";
                $.ajax({
                    url: emailsUrl,
                    method: 'GET',
                    success: function(response) {
                        // Count emails from response (parse the HTML)
                        var tempDiv = document.createElement('div');
                        tempDiv.innerHTML = response;
                        var emailRows = tempDiv.querySelectorAll('tbody tr');
                        var emailCount = 0;
                        emailRows.forEach(function(row) {
                            if (!row.querySelector('td[colspan]')) {
                                emailCount++;
                            }
                        });
                        $('#stat-emails').text(emailCount);
                    },
                    error: function() {
                        $('#stat-emails').text('0');
                    }
                });
            }

            function updateStats() {
                // Count SMS from the messages table
                var smsRows = $('#messages-table tbody tr.message-row').length;
                var hasEmptyState = $('#messages-table tbody tr td[colspan]').length > 0;
                var smsCount = hasEmptyState ? 0 : smsRows;
                $('#stat-sms').text(smsCount);

                // Get balance and units from the data attributes in the loaded content
                var metricsData = $('#sms-metrics-data');
                if (metricsData.length > 0) {
                    var balance = metricsData.data('balance') || 'N/A';
                    var units = metricsData.data('units') || 'N/A';
                    $('#stat-balance').text('BWP ' + balance);
                } else {
                    $('#stat-balance').text('N/A');
                }
            }

            $('#termId').trigger('change');
        });
    </script>
@endsection

@extends('layouts.master')
@section('title')
    Student Statement
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
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        /* Student search autocomplete */
        .search-container {
            position: relative;
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0 0 3px 3px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .search-results.show {
            display: block;
        }

        .search-result-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover {
            background: #f9fafb;
        }

        .search-result-item .student-name {
            font-weight: 600;
            color: #374151;
        }

        .search-result-item .student-details {
            font-size: 12px;
            color: #6b7280;
        }

        /* Student info card */
        .student-info-card {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            border-radius: 8px;
            padding: 20px;
            color: white;
            margin-bottom: 24px;
        }

        .student-info-card h4 {
            margin: 0 0 4px 0;
            font-weight: 700;
        }

        .student-info-card .student-meta {
            opacity: 0.9;
            font-size: 14px;
        }

        /* Summary cards */
        .summary-card {
            background: #f9fafb;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
        }

        .summary-card .value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #374151;
        }

        .summary-card .label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
        }

        .summary-card.balance-positive .value {
            color: #dc2626;
        }

        .summary-card.balance-zero .value {
            color: #059669;
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

        .amount-cell {
            font-weight: 600;
            color: #059669;
        }

        .balance-cell {
            font-weight: 600;
            color: #dc2626;
        }

        .debit-cell {
            color: #dc2626;
        }

        .credit-cell {
            color: #059669;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-paid { background: #d1fae5; color: #065f46; }
        .status-partial { background: #fef3c7; color: #92400e; }
        .status-outstanding { background: #fee2e2; color: #991b1b; }
        .status-issued { background: #fee2e2; color: #991b1b; }

        .method-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .method-cash { background: #d1fae5; color: #065f46; }
        .method-bank { background: #dbeafe; color: #1e40af; }
        .method-mobile { background: #ede9fe; color: #5b21b6; }
        .method-cheque { background: #ffedd5; color: #9a3412; }

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

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.3;
            margin-bottom: 12px;
        }

        .no-student-selected {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .no-student-selected i {
            font-size: 64px;
            opacity: 0.2;
            margin-bottom: 16px;
        }

        @media (max-width: 768px) {
            .fee-header {
                padding: 20px;
            }

            .fee-body {
                padding: 16px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('fees.reports.dashboard') }}">Back</a>
        @endslot
        @slot('title')
            Student Statement
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

    <div class="fee-container">
        <div class="fee-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Student Statement</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">View and print student fee statements</p>
                </div>
                <div class="col-md-6 text-end">
                    @if (isset($student))
                        <a href="{{ route('fees.reports.student-statement.pdf', ['student' => $student->id, 'year' => $filters['year'] ?? '']) }}"
                           class="btn btn-light" target="_blank">
                            <i class="fas fa-print me-1"></i> Print
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="fee-body">
            <div class="help-text">
                <div class="help-title">Student Statement</div>
                <div class="help-content">
                    Search for a student to view their complete fee statement including invoices, payments, and transaction history with running balance. Use the Print button to generate a PDF statement.
                </div>
            </div>

            <!-- Search and Filters -->
            <form method="GET" action="{{ route('fees.reports.student-statement') }}" id="searchForm">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-5 col-md-5 col-sm-12">
                                    <div class="search-container">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input type="text" class="form-control" id="studentSearch"
                                                   placeholder="Type student name or number..."
                                                   autocomplete="off"
                                                   value="{{ isset($student) ? $student->full_name . ' (' . $student->student_number . ')' : '' }}">
                                            <input type="hidden" name="student_id" id="studentId" value="{{ $filters['student_id'] ?? '' }}">
                                        </div>
                                        <div class="search-results" id="searchResults"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <select class="form-select" name="year">
                                        <option value="">All Years</option>
                                        @foreach ($years as $year)
                                            <option value="{{ $year }}" {{ ($filters['year'] ?? '') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-grow-1">
                                            <i class="fas fa-search me-1"></i> Search
                                        </button>
                                        <a href="{{ route('fees.reports.student-statement') }}" class="btn btn-light">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                        @if (isset($student))
                            @can('export-fee-reports')
                                <a href="{{ route('fees.reports.export.student-statement', ['student' => $student->id, 'year' => $filters['year'] ?? '']) }}"
                                   class="btn btn-success">
                                    <i class="fas fa-file-excel me-1"></i> Export to Excel
                                </a>
                            @endcan
                        @endif
                    </div>
                </div>
            </form>

            @if (isset($student))
                <!-- Student Info Card -->
                <div class="student-info-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4>{{ $student->full_name ?? ($student->first_name . ' ' . $student->last_name) }}</h4>
                            <div class="student-meta">
                                <i class="fas fa-id-card me-1"></i> {{ $student->student_number }} |
                                <i class="fas fa-graduation-cap me-1"></i> {{ $student->currentGrade->name ?? 'N/A' }} |
                                <i class="fas fa-user me-1"></i> {{ $student->sponsor->name ?? 'No Sponsor' }}
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <a href="{{ route('fees.collection.students.account', ['student' => $student->id]) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-external-link-alt me-1"></i> View Full Account
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Account Summary -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="summary-card">
                            <div class="value">{{ format_currency($balance['total_invoiced'] ?? 0) }}</div>
                            <div class="label">Total Invoiced</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="summary-card">
                            <div class="value">{{ format_currency($balance['total_paid'] ?? 0) }}</div>
                            <div class="label">Total Paid</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        @php
                            $balanceAmount = (float)($balance['balance'] ?? 0);
                        @endphp
                        <div class="summary-card {{ $balanceAmount > 0 ? 'balance-positive' : 'balance-zero' }}">
                            <div class="value">{{ format_currency($balanceAmount) }}</div>
                            <div class="label">Current Balance</div>
                        </div>
                    </div>
                </div>

                <!-- Invoices Table -->
                <h5 class="section-title"><i class="fas fa-file-invoice me-2"></i>Invoices</h5>
                @if ($invoices->count() > 0)
                    <div class="table-responsive mb-4">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Term</th>
                                    <th>Date</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Balance</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->invoice_number }}</td>
                                        <td><span class="term-badge">{{ $invoice->term->name ?? 'N/A' }}</span></td>
                                        <td>{{ $invoice->issued_at ? $invoice->issued_at->format('d M Y') : 'N/A' }}</td>
                                        <td class="text-end amount-cell">{{ format_currency($invoice->total_amount) }}</td>
                                        <td class="text-end">{{ format_currency($invoice->amount_paid) }}</td>
                                        <td class="text-end balance-cell">{{ format_currency($invoice->balance) }}</td>
                                        <td class="text-center">
                                            <span class="status-badge status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state mb-4">
                        <i class="fas fa-file-invoice d-block"></i>
                        <p class="mb-0">No invoices found</p>
                    </div>
                @endif

                <!-- Payments Table -->
                <h5 class="section-title"><i class="fas fa-coins me-2"></i>Payments</h5>
                @if ($payments->count() > 0)
                    <div class="table-responsive mb-4">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Date</th>
                                    <th class="text-end">Amount</th>
                                    <th>Method</th>
                                    <th>Invoice #</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($payments as $payment)
                                    @php
                                        $methodClass = match($payment->payment_method) {
                                            'cash' => 'method-cash',
                                            'bank_transfer' => 'method-bank',
                                            'mobile_money' => 'method-mobile',
                                            'cheque' => 'method-cheque',
                                            default => 'method-cash'
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ $payment->receipt_number }}</td>
                                        <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}</td>
                                        <td class="text-end amount-cell">{{ format_currency($payment->amount) }}</td>
                                        <td><span class="method-badge {{ $methodClass }}">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span></td>
                                        <td>{{ $payment->invoice->invoice_number ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state mb-4">
                        <i class="fas fa-coins d-block"></i>
                        <p class="mb-0">No payments recorded</p>
                    </div>
                @endif

                <!-- Transaction History with Running Balance -->
                <h5 class="section-title"><i class="fas fa-history me-2"></i>Transaction History</h5>
                @php
                    // Combine invoices and payments into a single timeline
                    $transactions = collect();

                    // Add invoices as debits
                    foreach ($invoices as $invoice) {
                        $transactions->push([
                            'date' => $invoice->issued_at ?? $invoice->created_at,
                            'description' => 'Invoice #' . $invoice->invoice_number . ' (' . ($invoice->term->name ?? 'N/A') . ')',
                            'debit' => (float)$invoice->total_amount,
                            'credit' => 0,
                            'type' => 'invoice',
                        ]);
                    }

                    // Add payments as credits
                    foreach ($payments as $payment) {
                        $transactions->push([
                            'date' => $payment->payment_date ?? $payment->created_at,
                            'description' => 'Payment #' . $payment->receipt_number,
                            'debit' => 0,
                            'credit' => (float)$payment->amount,
                            'type' => 'payment',
                        ]);
                    }

                    // Sort by date ascending
                    $transactions = $transactions->sortBy('date')->values();

                    // Calculate running balance
                    $runningBalance = 0;
                    foreach ($transactions as $index => $transaction) {
                        $runningBalance = $runningBalance + $transaction['debit'] - $transaction['credit'];
                        $transactions[$index]['balance'] = $runningBalance;
                    }
                @endphp

                @if ($transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Credit</th>
                                    <th class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction['date'] ? \Carbon\Carbon::parse($transaction['date'])->format('d M Y') : 'N/A' }}</td>
                                        <td>{{ $transaction['description'] }}</td>
                                        <td class="text-end debit-cell">{{ $transaction['debit'] > 0 ? format_currency($transaction['debit']) : '-' }}</td>
                                        <td class="text-end credit-cell">{{ $transaction['credit'] > 0 ? format_currency($transaction['credit']) : '-' }}</td>
                                        <td class="text-end balance-cell">{{ format_currency($transaction['balance']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-history d-block"></i>
                        <p class="mb-0">No transactions recorded</p>
                    </div>
                @endif
            @else
                <div class="no-student-selected">
                    <i class="fas fa-user-graduate d-block"></i>
                    <h5>Select a Student</h5>
                    <p class="text-muted">Search for a student using the search box above to view their fee statement</p>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeAlertDismissal();
            initializeStudentSearch();
        });

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

        function initializeStudentSearch() {
            const searchInput = document.getElementById('studentSearch');
            const searchResults = document.getElementById('searchResults');
            const studentIdInput = document.getElementById('studentId');
            let debounceTimer;

            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                clearTimeout(debounceTimer);

                if (query.length < 2) {
                    searchResults.classList.remove('show');
                    return;
                }

                debounceTimer = setTimeout(function() {
                    fetch('{{ route("fees.collection.students.search") }}?search=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(data => {
                            searchResults.innerHTML = '';

                            if (data.length === 0) {
                                searchResults.innerHTML = '<div class="search-result-item"><span class="text-muted">No students found</span></div>';
                            } else {
                                data.forEach(function(student) {
                                    const item = document.createElement('div');
                                    item.className = 'search-result-item';
                                    item.innerHTML = '<div class="student-name">' + student.full_name + '</div>' +
                                        '<div class="student-details">' + student.student_number + ' | ' + (student.grade_name || 'N/A') + '</div>';
                                    item.addEventListener('click', function() {
                                        searchInput.value = student.full_name + ' (' + student.student_number + ')';
                                        studentIdInput.value = student.id;
                                        searchResults.classList.remove('show');
                                    });
                                    searchResults.appendChild(item);
                                });
                            }

                            searchResults.classList.add('show');
                        })
                        .catch(function(error) {
                            console.error('Search error:', error);
                        });
                }, 300); // 300ms debounce
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.remove('show');
                }
            });

            // Re-show dropdown on focus if there are results
            searchInput.addEventListener('focus', function() {
                if (searchResults.innerHTML.trim() !== '' && this.value.length >= 2) {
                    searchResults.classList.add('show');
                }
            });
        }
    </script>
@endsection

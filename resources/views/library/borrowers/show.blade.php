@extends('layouts.master')
@section('title')
    {{ $borrower->full_name }} - Borrower Profile
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

        .borrower-info {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        .borrower-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .borrower-name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .borrower-meta {
            font-size: 14px;
            opacity: 0.85;
        }

        .borrower-type-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 8px;
        }

        .badge-type-student {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .badge-type-staff {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .quick-stats {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.75;
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

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-checked_out {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-overdue {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-returned {
            background: #d1fae5;
            color: #065f46;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-partial {
            background: #fed7aa;
            color: #9a3412;
        }

        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }

        .status-waived {
            background: #e5e7eb;
            color: #374151;
        }

        .text-overdue {
            color: #dc2626;
            font-weight: 600;
        }

        .text-remaining {
            color: #059669;
        }

        .filter-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .filter-bar .form-control {
            max-width: 180px;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
        }

        .filter-bar .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .btn-filter {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-filter:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .fines-total-row td {
            font-weight: 700;
            background: #f9fafb;
            border-top: 2px solid #e5e7eb;
        }

        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.3;
            margin-bottom: 12px;
        }

        .empty-state p {
            font-size: 15px;
            margin: 0;
        }

        .empty-state-success i {
            color: #059669;
            opacity: 0.5;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 6px 14px;
            border-radius: 3px;
            font-size: 13px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            text-decoration: none;
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

            .quick-stats {
                gap: 16px;
            }

            .stat-item h4 {
                font-size: 1.25rem;
            }

            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-bar .form-control {
                max-width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="javascript:void(0);">Library</a>
        @endslot
        @slot('li_2')
            <a class="text-muted font-size-14" href="{{ route('library.borrowers.index') }}">Borrowers</a>
        @endslot
        @slot('title')
            {{ $borrower->full_name }}
        @endslot
    @endcomponent

    <div class="library-container">
        <div class="library-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="borrower-info">
                        <div class="borrower-avatar">
                            <i class="fas {{ $type === 'student' ? 'fa-user-graduate' : 'fa-user-tie' }}"></i>
                        </div>
                        <div>
                            <div class="borrower-name">
                                {{ $borrower->full_name }}
                                <span
                                    class="borrower-type-badge {{ $type === 'student' ? 'badge-type-student' : 'badge-type-staff' }}">
                                    {{ $type === 'student' ? 'Student' : 'Staff' }}
                                </span>
                            </div>
                            <div class="borrower-meta">
                                @if ($type === 'student')
                                    {{ $borrower->exam_number ?? ($borrower->id_number ?? 'ID: ' . $borrower->id) }}
                                    @if (optional($borrower->currentGrade)->name)
                                        &middot; {{ $borrower->currentGrade->name }}
                                    @endif
                                @else
                                    {{ $borrower->id_number ?? 'ID: ' . $borrower->id }}
                                    @if ($borrower->position || $borrower->department)
                                        &middot; {{ $borrower->position ?? $borrower->department }}
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="quick-stats justify-content-md-end">
                        <div class="stat-item">
                            <h4 class="text-white">{{ $currentLoans->count() }}</h4>
                            <small>Current Loans</small>
                        </div>
                        <div class="stat-item">
                            <h4 class="text-white">{{ $history->total() }}</h4>
                            <small>History</small>
                        </div>
                        <div class="stat-item">
                            <h4 class="text-white">P{{ number_format($totalFinesOutstanding, 2) }}</h4>
                            <small>Fines Due</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="library-body">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start flex-wrap" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#currentLoans" role="tab">
                                <i class="fas fa-book-open me-2 text-muted"></i>Current Loans
                                @if ($currentLoans->count() > 0)
                                    <span class="badge bg-primary ms-1">{{ $currentLoans->count() }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#borrowingHistory" role="tab">
                                <i class="fas fa-history me-2 text-muted"></i>Borrowing History
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#outstandingFines" role="tab">
                                <i class="fas fa-money-bill-wave me-2 text-muted"></i>Outstanding Fines
                                @if ($outstandingFines->count() > 0)
                                    <span class="badge bg-danger ms-1">{{ $outstandingFines->count() }}</span>
                                @endif
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3">
                        {{-- Current Loans Tab --}}
                        <div class="tab-pane active" id="currentLoans" role="tabpanel">
                            @if ($currentLoans->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Book Title</th>
                                                <th>Accession No.</th>
                                                <th>Checkout Date</th>
                                                <th>Due Date</th>
                                                <th>Status</th>
                                                <th>Days</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($currentLoans as $loan)
                                                @php
                                                    $daysRemaining = now()
                                                        ->startOfDay()
                                                        ->diffInDays($loan->due_date->startOfDay(), false);
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <strong>{{ optional(optional($loan->copy)->book)->title ?? 'Unknown' }}</strong>
                                                    </td>
                                                    <td>{{ optional($loan->copy)->accession_number ?? 'N/A' }}</td>
                                                    <td>{{ $loan->checkout_date->format('M d, Y') }}</td>
                                                    <td>{{ $loan->due_date->format('M d, Y') }}</td>
                                                    <td>
                                                        <span class="status-badge status-{{ $loan->status }}">
                                                            {{ str_replace('_', ' ', $loan->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if ($daysRemaining < 0)
                                                            <span class="text-overdue">{{ abs($daysRemaining) }} days
                                                                overdue</span>
                                                        @elseif($daysRemaining == 0)
                                                            <span class="text-overdue">Due today</span>
                                                        @else
                                                            <span class="text-remaining">{{ $daysRemaining }} days
                                                                left</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="empty-state">
                                    <i class="fas fa-book d-block"></i>
                                    <p>No active loans</p>
                                </div>
                            @endif
                        </div>

                        {{-- Borrowing History Tab --}}
                        <div class="tab-pane" id="borrowingHistory" role="tabpanel">
                            <form method="GET"
                                action="{{ route('library.borrowers.show', ['type' => $type, 'id' => $borrower->id]) }}">
                                <div class="filter-bar">
                                    <label class="form-label mb-0" style="font-weight: 500; color: #374151;">From:</label>
                                    <input type="date" name="from" class="form-control"
                                        value="{{ request('from') }}">
                                    <label class="form-label mb-0" style="font-weight: 500; color: #374151;">To:</label>
                                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                                    <button type="submit" class="btn-filter">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                    @if (request('from') || request('to'))
                                        <a href="{{ route('library.borrowers.show', ['type' => $type, 'id' => $borrower->id]) }}"
                                            class="btn btn-light btn-sm">
                                            <i class="fas fa-times me-1"></i> Clear
                                        </a>
                                    @endif
                                </div>
                            </form>

                            @if ($history->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Book Title</th>
                                                <th>Accession No.</th>
                                                <th>Checkout Date</th>
                                                <th>Due Date</th>
                                                <th>Return Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($history as $record)
                                                <tr>
                                                    <td>
                                                        <strong>{{ optional(optional($record->copy)->book)->title ?? 'Unknown' }}</strong>
                                                    </td>
                                                    <td>{{ optional($record->copy)->accession_number ?? 'N/A' }}</td>
                                                    <td>{{ $record->checkout_date->format('M d, Y') }}</td>
                                                    <td>{{ $record->due_date->format('M d, Y') }}</td>
                                                    <td>{{ $record->return_date ? $record->return_date->format('M d, Y') : '-' }}
                                                    </td>
                                                    <td>
                                                        <span class="status-badge status-{{ $record->status }}">
                                                            {{ str_replace('_', ' ', $record->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    {{ $history->appends(request()->query())->links() }}
                                </div>
                            @else
                                <div class="empty-state">
                                    <i class="fas fa-history d-block"></i>
                                    <p>No borrowing
                                        history{{ request('from') || request('to') ? ' for the selected date range' : '' }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        {{-- Outstanding Fines Tab --}}
                        <div class="tab-pane" id="outstandingFines" role="tabpanel">
                            @if ($outstandingFines->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Fine Date</th>
                                                <th>Book Title</th>
                                                <th>Fine Type</th>
                                                <th class="text-end">Amount</th>
                                                <th class="text-end">Paid</th>
                                                <th class="text-end">Waived</th>
                                                <th class="text-end">Outstanding</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($outstandingFines as $fine)
                                                <tr>
                                                    <td>{{ $fine->fine_date ? $fine->fine_date->format('M d, Y') : '-' }}
                                                    </td>
                                                    <td>
                                                        <strong>{{ optional(optional(optional($fine->transaction)->copy)->book)->title ?? 'Unknown' }}</strong>
                                                    </td>
                                                    <td>{{ ucfirst(str_replace('_', ' ', $fine->fine_type ?? '-')) }}</td>
                                                    <td class="text-end">P{{ number_format($fine->amount, 2) }}</td>
                                                    <td class="text-end">P{{ number_format($fine->amount_paid, 2) }}</td>
                                                    <td class="text-end">P{{ number_format($fine->amount_waived, 2) }}
                                                    </td>
                                                    <td class="text-end">
                                                        <strong>P{{ number_format($fine->outstanding, 2) }}</strong></td>
                                                    <td>
                                                        <span class="status-badge status-{{ $fine->status }}">
                                                            {{ ucfirst($fine->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="fines-total-row">
                                                <td colspan="6" class="text-end">Total Outstanding:</td>
                                                <td class="text-end">P{{ number_format($totalFinesOutstanding, 2) }}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="empty-state empty-state-success">
                                    <i class="fas fa-check-circle d-block"></i>
                                    <p>No outstanding fines</p>
                                </div>
                            @endif
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
            var tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
            tabLinks.forEach(function(tabLink) {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    var activeTabHref = event.target.getAttribute('href');
                    localStorage.setItem('borrowerProfileActiveTab', activeTabHref);
                });
            });

            // Restore active tab from localStorage
            var activeTab = localStorage.getItem('borrowerProfileActiveTab');
            if (activeTab) {
                var tabTriggerEl = document.querySelector('.nav-link[href="' + activeTab + '"]');
                if (tabTriggerEl) {
                    var tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                }
            }
        });
    </script>
@endsection

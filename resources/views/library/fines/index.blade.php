@extends('layouts.master')
@section('title')
    Fines & Penalties
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

        /* Controls / Filter Bar */
        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        /* Table Tweaks */
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

        .table td {
            font-size: 13px;
            vertical-align: middle;
        }

        .status-badge {
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 10px;
            font-weight: 600;
        }

        .type-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 10px;
        }

        /* Amount cells */
        .amount-cell {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            font-size: 13px;
        }

        .amount-outstanding {
            color: #e74a3b;
        }

        .amount-paid {
            color: #1cc88a;
        }

        .amount-waived {
            color: #858796;
        }

        /* Action Buttons */
        .btn-action {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 3px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
        }

        .empty-state i {
            font-size: 3rem;
            color: #1cc88a;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6b7280;
            margin: 0;
        }

        /* Modal tweaks */
        .modal-header {
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
        }

        .fine-detail {
            background: #f8f9fa;
            border-radius: 3px;
            padding: 12px;
            margin-bottom: 16px;
            border-left: 4px solid #4e73df;
        }

        .fine-detail dt {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
        }

        .fine-detail dd {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .library-header {
                padding: 20px;
            }

            .library-body {
                padding: 16px;
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
            Fines & Penalties
        @endslot
    @endcomponent

    <div class="library-container">
        {{-- Page Header with Stats --}}
        <div class="library-header">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h3 style="margin:0;">Fines & Penalties</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        Manage library fines, record payments, and process waivers
                    </p>
                </div>
                <div class="col-md-8">
                    <div class="row text-center">
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">P{{ number_format($totalFines, 2) }}</h4>
                                <small class="opacity-75">Total Fines</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">P{{ number_format($totalPaid, 2) }}</h4>
                                <small class="opacity-75">Total Paid</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">P{{ number_format($totalWaived, 2) }}</h4>
                                <small class="opacity-75">Total Waived</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">P{{ number_format($totalOutstanding, 2) }}</h4>
                                <small class="opacity-75">Outstanding</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="library-body">
            {{-- Filter Bar (admissions-style inline controls) --}}
            <form action="{{ route('library.fines.index') }}" method="GET">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-10 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                                        <input type="text" name="borrower_search" class="form-control" placeholder="Search borrower..." value="{{ request('borrower_search') }}">
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="waived" {{ request('status') === 'waived' ? 'selected' : '' }}>Waived</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <select name="fine_type" class="form-select">
                                        <option value="">All Types</option>
                                        <option value="overdue" {{ request('fine_type') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                                        <option value="lost" {{ request('fine_type') === 'lost' ? 'selected' : '' }}>Lost Book</option>
                                        <option value="damage" {{ request('fine_type') === 'damage' ? 'selected' : '' }}>Damage</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <input type="date" name="date_from" class="form-control" placeholder="From" value="{{ request('date_from') }}">
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <input type="date" name="date_to" class="form-control" placeholder="To" value="{{ request('date_to') }}">
                                </div>
                                <div class="col-lg-1 col-md-12">
                                    <div class="d-flex gap-1">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bx bx-filter-alt"></i>
                                        </button>
                                        <a href="{{ route('library.fines.index') }}" class="btn btn-light w-100">
                                            <i class="bx bx-reset"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Fines Table --}}
            @if($fines->isEmpty())
                <div class="empty-state">
                    <i class="bx bx-check-circle d-block"></i>
                    <h5>No Fines Found</h5>
                    <p>There are currently no fines matching the selected criteria.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fine Date</th>
                                <th>Borrower</th>
                                <th>Book Title</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Waived</th>
                                <th class="text-end">Outstanding</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fines as $fine)
                                @php
                                    $borrowerName = '-';
                                    if ($fine->borrower) {
                                        $borrowerName = $fine->borrower->full_name ?? $fine->borrower->name ?? '-';
                                    }
                                    $bookTitle = $fine->transaction->copy->book->title ?? '-';
                                    $outstanding = $fine->outstanding;
                                @endphp
                                <tr>
                                    <td>{{ $fine->fine_date->format('d M Y') }}</td>
                                    <td>
                                        {{ $borrowerName }}
                                        @if($fine->borrower_type === 'student')
                                            <span class="badge bg-primary type-badge">Student</span>
                                        @else
                                            <span class="badge bg-secondary type-badge">Staff</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($bookTitle, 40) }}</td>
                                    <td>
                                        @if($fine->fine_type === 'overdue')
                                            <span class="badge bg-warning text-dark type-badge">Overdue</span>
                                        @elseif($fine->fine_type === 'lost')
                                            <span class="badge bg-danger type-badge">Lost</span>
                                        @elseif($fine->fine_type === 'damage')
                                            <span class="badge bg-info type-badge">Damage</span>
                                        @else
                                            <span class="badge bg-secondary type-badge">{{ ucfirst($fine->fine_type) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end amount-cell">P{{ number_format($fine->amount, 2) }}</td>
                                    <td class="text-end amount-cell amount-paid">P{{ number_format($fine->amount_paid, 2) }}</td>
                                    <td class="text-end amount-cell amount-waived">P{{ number_format($fine->amount_waived, 2) }}</td>
                                    <td class="text-end amount-cell amount-outstanding">P{{ number_format($outstanding, 2) }}</td>
                                    <td>
                                        @if($fine->status === 'pending')
                                            <span class="badge bg-warning text-dark status-badge">Pending</span>
                                        @elseif($fine->status === 'partial')
                                            <span class="badge bg-info status-badge">Partial</span>
                                        @elseif($fine->status === 'paid')
                                            <span class="badge bg-success status-badge">Paid</span>
                                        @elseif($fine->status === 'waived')
                                            <span class="badge bg-secondary status-badge">Waived</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($fine->amount_paid > 0)
                                            <a href="{{ route('library.fines.receipt', $fine) }}" target="_blank" class="btn btn-outline-info btn-action" title="Print Receipt">
                                                <i class="bx bx-printer"></i>
                                            </a>
                                        @endif
                                        @if(in_array($fine->status, ['pending', 'partial']))
                                            <button type="button"
                                                class="btn btn-success btn-action btn-pay"
                                                data-fine-id="{{ $fine->id }}"
                                                data-fine-amount="{{ $fine->amount }}"
                                                data-fine-outstanding="{{ number_format($outstanding, 2, '.', '') }}"
                                                data-borrower-name="{{ $borrowerName }}"
                                                data-book-title="{{ $bookTitle }}"
                                                title="Record Payment">
                                                <i class="bx bx-credit-card"></i> Pay
                                            </button>
                                            @can('waive-library-fines')
                                                <button type="button"
                                                    class="btn btn-secondary btn-action btn-waive"
                                                    data-fine-id="{{ $fine->id }}"
                                                    data-fine-amount="{{ $fine->amount }}"
                                                    data-fine-outstanding="{{ number_format($outstanding, 2, '.', '') }}"
                                                    data-borrower-name="{{ $borrowerName }}"
                                                    data-book-title="{{ $bookTitle }}"
                                                    title="Waive Fine">
                                                    <i class="bx bx-shield"></i> Waive
                                                </button>
                                            @endcan
                                        @else
                                            @if($fine->amount_paid == 0)
                                                <span class="text-muted" style="font-size: 12px;">Settled</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Payment Modal --}}
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="paymentForm">
                    @csrf
                    <div class="modal-body">
                        <div class="fine-detail">
                            <dl class="row mb-0">
                                <dt class="col-5">Borrower</dt>
                                <dd class="col-7" id="payBorrowerName">-</dd>
                                <dt class="col-5">Book</dt>
                                <dd class="col-7" id="payBookTitle">-</dd>
                                <dt class="col-5">Outstanding</dt>
                                <dd class="col-7" id="payOutstanding">P0.00</dd>
                            </dl>
                        </div>
                        <div class="mb-3">
                            <label for="payAmount" class="form-label">Payment Amount (P) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="payAmount" name="amount" step="0.01" min="0.01" required>
                            <div class="form-text">Enter partial or full payment amount.</div>
                        </div>
                        <div class="mb-3">
                            <label for="payNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="payNotes" name="notes" rows="2" maxlength="500" placeholder="Optional payment notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-loading" id="btnSubmitPayment">
                            <span class="btn-text"><i class="bx bx-credit-card"></i> Record Payment</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Waive Modal --}}
    @can('waive-library-fines')
        <div class="modal fade" id="waiveModal" tabindex="-1" aria-labelledby="waiveModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="waiveModalLabel">Waive Fine</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="waiveForm">
                        @csrf
                        <div class="modal-body">
                            <div class="fine-detail">
                                <dl class="row mb-0">
                                    <dt class="col-5">Borrower</dt>
                                    <dd class="col-7" id="waiveBorrowerName">-</dd>
                                    <dt class="col-5">Book</dt>
                                    <dd class="col-7" id="waiveBookTitle">-</dd>
                                    <dt class="col-5">Outstanding</dt>
                                    <dd class="col-7" id="waiveOutstanding">P0.00</dd>
                                </dl>
                            </div>
                            <div class="mb-3">
                                <label for="waiveAmount" class="form-label">Waiver Amount (P) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="waiveAmount" name="amount" step="0.01" min="0.01" required>
                                <div class="form-text">Enter partial or full waiver amount.</div>
                            </div>
                            <div class="mb-3">
                                <label for="waiveReason" class="form-label">Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="waiveReason" name="reason" rows="3" minlength="5" maxlength="500" required placeholder="Provide reason for waiver (min 5 characters)..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-secondary btn-loading" id="btnSubmitWaive">
                                <span class="btn-text"><i class="bx bx-shield"></i> Apply Waiver</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ==================== PAYMENT MODAL ====================
            let paymentFineId = null;

            document.querySelectorAll('.btn-pay').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    paymentFineId = this.dataset.fineId;
                    const outstanding = this.dataset.fineOutstanding;

                    document.getElementById('payBorrowerName').textContent = this.dataset.borrowerName;
                    document.getElementById('payBookTitle').textContent = this.dataset.bookTitle;
                    document.getElementById('payOutstanding').textContent = 'P' + outstanding;
                    document.getElementById('payAmount').max = outstanding;
                    document.getElementById('payAmount').value = '';
                    document.getElementById('payNotes').value = '';

                    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
                    modal.show();
                });
            });

            document.getElementById('paymentForm').addEventListener('submit', function (e) {
                e.preventDefault();

                const submitBtn = document.getElementById('btnSubmitPayment');
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;

                const formData = new FormData(this);

                fetch('/library/fines/' + paymentFineId + '/payment', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;

                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Recorded',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function () {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Payment Failed',
                            text: data.message || 'An error occurred while recording the payment.',
                        });
                    }
                })
                .catch(function (error) {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An unexpected error occurred. Please try again.',
                    });
                });
            });

            // ==================== WAIVE MODAL ====================
            let waiveFineId = null;

            document.querySelectorAll('.btn-waive').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    waiveFineId = this.dataset.fineId;
                    const outstanding = this.dataset.fineOutstanding;

                    document.getElementById('waiveBorrowerName').textContent = this.dataset.borrowerName;
                    document.getElementById('waiveBookTitle').textContent = this.dataset.bookTitle;
                    document.getElementById('waiveOutstanding').textContent = 'P' + outstanding;
                    document.getElementById('waiveAmount').max = outstanding;
                    document.getElementById('waiveAmount').value = '';
                    document.getElementById('waiveReason').value = '';

                    const modal = new bootstrap.Modal(document.getElementById('waiveModal'));
                    modal.show();
                });
            });

            const waiveForm = document.getElementById('waiveForm');
            if (waiveForm) {
                waiveForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const submitBtn = document.getElementById('btnSubmitWaive');
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;

                    const formData = new FormData(this);

                    fetch('/library/fines/' + waiveFineId + '/waive', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    })
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        submitBtn.classList.remove('loading');
                        submitBtn.disabled = false;

                        if (data.success) {
                            bootstrap.Modal.getInstance(document.getElementById('waiveModal')).hide();
                            Swal.fire({
                                icon: 'success',
                                title: 'Waiver Applied',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(function () {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Waiver Failed',
                                text: data.message || 'An error occurred while applying the waiver.',
                            });
                        }
                    })
                    .catch(function (error) {
                        submitBtn.classList.remove('loading');
                        submitBtn.disabled = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred. Please try again.',
                        });
                    });
                });
            }
        });
    </script>
@endsection

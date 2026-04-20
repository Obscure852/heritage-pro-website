@extends('layouts.master')
@section('title')
    {{ $book->title }}
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

        /* Book Detail Layout */
        .book-detail-grid {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 32px;
        }

        .book-cover-large {
            width: 200px;
            height: 280px;
            border-radius: 3px;
            overflow: hidden;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .book-cover-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .book-cover-large .placeholder-icon {
            font-size: 64px;
            color: #9ca3af;
        }

        /* Info Items */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .info-item {
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-item .info-label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-item .info-value {
            font-size: 14px;
            color: #1f2937;
            font-weight: 500;
        }

        .info-item.full-width {
            grid-column: 1 / -1;
        }

        /* Copy Status Badges */
        .copy-status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .copy-status-available {
            background: #d1fae5;
            color: #065f46;
        }

        .copy-status-checked_out {
            background: #dbeafe;
            color: #1e40af;
        }

        .copy-status-on_hold {
            background: #fef3c7;
            color: #92400e;
        }

        .copy-status-lost {
            background: #fee2e2;
            color: #991b1b;
        }

        .copy-status-damaged {
            background: #ffedd5;
            color: #9a3412;
        }

        .copy-status-withdrawn {
            background: #f3f4f6;
            color: #4b5563;
        }

        .copy-status-in_repair {
            background: #fef3c7;
            color: #92400e;
        }

        /* Transaction Status Badges */
        .txn-status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .txn-status-checked_out {
            background: #dbeafe;
            color: #1e40af;
        }

        .txn-status-returned {
            background: #d1fae5;
            color: #065f46;
        }

        .txn-status-overdue {
            background: #fee2e2;
            color: #991b1b;
        }

        .txn-status-lost {
            background: #f3f4f6;
            color: #4b5563;
        }

        /* Copies Summary */
        .copies-summary {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            padding: 12px 0;
            margin-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .copies-summary .summary-item {
            font-size: 13px;
            color: #6b7280;
        }

        .copies-summary .summary-item strong {
            color: #1f2937;
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

        /* Reserve Button Loading */
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

        @media (max-width: 768px) {
            .book-detail-grid {
                grid-template-columns: 1fr;
            }

            .book-cover-large {
                margin: 0 auto;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

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
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="javascript:void(0);">Library</a>
        @endslot
        @slot('li_2')
            <a class="text-muted font-size-14" href="{{ route('library.catalog.index') }}">Catalog</a>
        @endslot
        @slot('title')
            {{ $book->title }}
        @endslot
    @endcomponent

    <div class="library-container">
        <div class="library-header">
            <h4 class="mb-1 text-white">{{ $book->title }}</h4>
            <p class="mb-0 opacity-75">{{ $book->authors_list }}</p>
        </div>
        <div class="library-body">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start flex-wrap" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#bookDetails" role="tab">
                                <i class="fas fa-info-circle me-2 text-muted"></i>Item Details
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#copies" role="tab">
                                <i class="fas fa-copy me-2 text-muted"></i>Copies
                                <span class="badge bg-secondary ms-1">{{ $book->copies->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#circulationHistory" role="tab">
                                <i class="fas fa-history me-2 text-muted"></i>Circulation History
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        {{-- Book Details Tab --}}
                        <div class="tab-pane active" id="bookDetails" role="tabpanel">
                            <div class="book-detail-grid">
                                <div>
                                    <div class="book-cover-large">
                                        @if ($book->cover_image_url)
                                            <img src="{{ $book->cover_image_url }}" alt="{{ $book->title }}">
                                        @else
                                            <i class="fas fa-book placeholder-icon"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-label">Title</div>
                                        <div class="info-value">{{ $book->title }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Author(s)</div>
                                        <div class="info-value">{{ $book->authors_list }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Publisher</div>
                                        <div class="info-value">{{ $book->publisher->name ?? '-' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">ISBN</div>
                                        <div class="info-value">{{ $book->isbn ?? '-' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Dewey Decimal</div>
                                        <div class="info-value">{{ $book->dewey_decimal ?? '-' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Category</div>
                                        <div class="info-value">{{ $book->genre ?? '-' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Grade Level</div>
                                        <div class="info-value">{{ $book->grade->name ?? '-' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Language</div>
                                        <div class="info-value">{{ $book->language ?? '-' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Item Type</div>
                                        <div class="info-value">{{ $book->format ?? '-' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Reading Level</div>
                                        <div class="info-value">{{ $book->reading_level ?? '-' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Condition</div>
                                        <div class="info-value" style="text-transform: capitalize;">
                                            {{ $book->condition ?? '-' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Pages</div>
                                        <div class="info-value">{{ $book->pages ?? '-' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Publication Year</div>
                                        <div class="info-value">{{ $book->publication_year ?? '-' }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Edition</div>
                                        <div class="info-value">{{ $book->edition ?? '-' }}</div>
                                    </div>
                                    @if ($book->keywords)
                                        <div class="info-item full-width">
                                            <div class="info-label">Keywords</div>
                                            <div class="info-value">{{ $book->keywords }}</div>
                                        </div>
                                    @endif
                                    @if ($book->description)
                                        <div class="info-item full-width">
                                            <div class="info-label">Description</div>
                                            <div class="info-value">{{ $book->description }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Copies Tab --}}
                        <div class="tab-pane" id="copies" role="tabpanel">
                            @php
                                $totalCopies = $book->copies->count();
                                $availableCopies = $book->copies->where('status', 'available')->count();
                                $checkedOutCopies = $book->copies->where('status', 'checked_out')->count();
                                $onHoldCopies = $book->copies->where('status', 'on_hold')->count();
                                $lostCopies = $book->copies->where('status', 'lost')->count();
                            @endphp
                            <div class="copies-summary">
                                <div class="summary-item">Total: <strong>{{ $totalCopies }}</strong></div>
                                <div class="summary-item">Available: <strong
                                        class="text-success">{{ $availableCopies }}</strong></div>
                                <div class="summary-item">Checked Out: <strong
                                        class="text-primary">{{ $checkedOutCopies }}</strong></div>
                                @if ($onHoldCopies > 0)
                                    <div class="summary-item">On Hold: <strong
                                            class="text-warning">{{ $onHoldCopies }}</strong></div>
                                @endif
                                @if ($lostCopies > 0)
                                    <div class="summary-item">Lost: <strong
                                            class="text-danger">{{ $lostCopies }}</strong></div>
                                @endif
                            </div>

                            @if ($availableCopies === 0 && $totalCopies > 0)
                                @can('access-library')
                                    @if (Route::has('library.catalog.reserve'))
                                        <div class="mb-3">
                                            <button type="button" class="btn btn-primary btn-loading" id="reserveBtn"
                                                data-url="{{ route('library.catalog.reserve', $book) }}"
                                                style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border: none;">
                                                <span class="btn-text"><i class="bx bx-book-bookmark me-1"></i> Reserve This
                                                    Item</span>
                                                <span class="btn-spinner d-none">
                                                    <span class="spinner-border spinner-border-sm me-2" role="status"
                                                        aria-hidden="true"></span>
                                                    Reserving...
                                                </span>
                                            </button>
                                            <small class="text-muted d-block mt-1">All copies are currently unavailable.
                                                Reserve to join the hold queue.</small>
                                        </div>
                                    @endif
                                @endcan
                            @endif

                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Accession Number</th>
                                            <th>Status</th>
                                            <th>Current Borrower</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($book->copies as $copy)
                                            <tr>
                                                <td class="fw-semibold">{{ $copy->accession_number }}</td>
                                                <td>
                                                    <span class="copy-status-badge copy-status-{{ $copy->status }}">
                                                        {{ str_replace('_', ' ', $copy->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if (in_array($copy->status, ['checked_out', 'overdue']) && $copy->currentTransaction)
                                                        {{ $copy->currentTransaction->borrower->full_name ?? ($copy->currentTransaction->borrower->name ?? '-') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3">
                                                    <div class="text-center text-muted" style="padding: 20px 0;">
                                                        No copies registered for this item.
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Circulation History Tab --}}
                        <div class="tab-pane" id="circulationHistory" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Borrower</th>
                                            <th>Copy</th>
                                            <th>Checkout Date</th>
                                            <th>Due Date</th>
                                            <th>Return Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($circulationHistory as $transaction)
                                            <tr>
                                                <td>{{ $transaction->borrower->full_name ?? ($transaction->borrower->name ?? '-') }}
                                                </td>
                                                <td>{{ $transaction->copy->accession_number ?? '-' }}</td>
                                                <td>{{ $transaction->checkout_date?->format('M d, Y') ?? '-' }}</td>
                                                <td>{{ $transaction->due_date?->format('M d, Y') ?? '-' }}</td>
                                                <td>{{ $transaction->return_date?->format('M d, Y') ?? '-' }}</td>
                                                <td>
                                                    <span class="txn-status-badge txn-status-{{ $transaction->status }}">
                                                        {{ str_replace('_', ' ', $transaction->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6">
                                                    <div class="text-center text-muted" style="padding: 20px 0;">
                                                        No circulation history for this item.
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($circulationHistory->hasPages())
                                <div class="mt-3">
                                    {{ $circulationHistory->links() }}
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
            // Tab persistence via localStorage
            const storageKey = 'catalogBookDetailActiveTab';
            const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');

            tabLinks.forEach(tabLink => {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    const activeTabHref = event.target.getAttribute('href');
                    localStorage.setItem(storageKey, activeTabHref);
                });
            });

            // Restore active tab from localStorage
            const activeTab = localStorage.getItem(storageKey);
            if (activeTab) {
                const tabTriggerEl = document.querySelector(`.nav-link[href="${activeTab}"]`);
                if (tabTriggerEl) {
                    const tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                }
            }

            // Reserve button AJAX
            const reserveBtn = document.getElementById('reserveBtn');
            if (reserveBtn) {
                reserveBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Reserve This Item?',
                        text: 'You will be added to the hold queue and notified when a copy becomes available.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Reserve',
                        cancelButtonText: 'Cancel',
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            reserveBtn.classList.add('loading');
                            reserveBtn.disabled = true;

                            fetch(reserveBtn.dataset.url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                            }).then(function(r) {
                                return r.json();
                            }).then(function(data) {
                                reserveBtn.classList.remove('loading');
                                reserveBtn.disabled = false;

                                if (data.success) {
                                    Swal.fire('Reserved!', data.message, 'success').then(
                                        function() {
                                            location.reload();
                                        });
                                } else {
                                    Swal.fire('Cannot Reserve', data.message || data.error,
                                        'error');
                                }
                            }).catch(function() {
                                reserveBtn.classList.remove('loading');
                                reserveBtn.disabled = false;
                                Swal.fire('Error',
                                    'Something went wrong. Please try again.', 'error');
                            });
                        }
                    });
                });
            }
        });
    </script>
@endsection

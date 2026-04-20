@extends('layouts.master')
@section('title')
    Reservations
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

        /* Filter Bar */
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .filter-bar .filter-link {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            color: #6b7280;
            background: #f3f4f6;
            transition: all 0.2s;
        }

        .filter-bar .filter-link:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .filter-bar .filter-link.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
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
            color: #36b9cc;
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

        /* Expired highlight */
        .text-expired {
            color: #e74a3b;
            font-weight: 600;
        }

        /* Queue position badge */
        .queue-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #e5e7eb;
            font-size: 12px;
            font-weight: 700;
            color: #374151;
        }

        /* Borrower Choices.js Override */
        .borrower-choices .choices__inner {
            background: #fff;
            border: 1px solid #ced4da;
            border-radius: 3px;
            min-height: 38px;
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
            <a class="text-muted font-size-14" href="{{ route('library.catalog.index') }}">Library</a>
        @endslot
        @slot('title')
            Reservations
        @endslot
    @endcomponent

    <div class="library-container">
        {{-- Page Header with Stats --}}
        <div class="library-header">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h3 style="margin:0;">Reservations</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        Manage book reservation queue and hold pickups
                    </p>
                </div>
                <div class="col-md-8">
                    <div class="row text-center align-items-center">
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['pending'] }}</h4>
                                <small class="opacity-75">Pending</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['ready'] }}</h4>
                                <small class="opacity-75">Ready for Pickup</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['fulfilled_today'] }}</h4>
                                <small class="opacity-75">Fulfilled Today</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['expired_today'] }}</h4>
                                <small class="opacity-75">Expired Today</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="library-body">
            {{-- Status Filter Bar --}}
            @php
                $currentStatus = request('status', '');
                $filters = [
                    '' => 'Active',
                    'pending' => 'Pending',
                    'ready' => 'Ready',
                    'fulfilled' => 'Fulfilled',
                    'expired' => 'Expired',
                    'cancelled' => 'Cancelled',
                ];
            @endphp
            <div class="filter-bar">
                @foreach($filters as $value => $label)
                    <a href="{{ route('library.reservations.index', $value ? ['status' => $value] : []) }}"
                       class="filter-link {{ $currentStatus === $value ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
                <button type="button" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#placeReservationModal">
                    <i class="fas fa-plus me-1"></i> Place Reservation
                </button>
            </div>

            {{-- Reservations Table --}}
            @if($reservations->isEmpty())
                <div class="empty-state">
                    <i class="bx bx-book-bookmark d-block"></i>
                    <h5>No Reservations Found</h5>
                    <p>There are currently no reservations matching the selected filter.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Book</th>
                                <th>Borrower</th>
                                <th class="text-center">Queue</th>
                                <th>Status</th>
                                <th>Placed</th>
                                <th>Expires</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservations as $index => $reservation)
                                @php
                                    $borrowerName = '-';
                                    if ($reservation->borrower) {
                                        $borrowerName = $reservation->borrower->full_name
                                            ?? $reservation->borrower->name
                                            ?? '-';
                                    }
                                    $bookTitle = $reservation->book->title ?? '-';
                                    $isExpiredHold = $reservation->status === 'ready'
                                        && $reservation->expires_at
                                        && $reservation->expires_at->isPast();
                                @endphp
                                <tr>
                                    <td>{{ $reservations->firstItem() + $index }}</td>
                                    <td>
                                        @if($reservation->book)
                                            <a href="{{ route('library.catalog.show', $reservation->book) }}">
                                                {{ Str::limit($bookTitle, 40) }}
                                            </a>
                                        @else
                                            {{ Str::limit($bookTitle, 40) }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ $borrowerName }}
                                        @if($reservation->borrower_type === 'student')
                                            <span class="badge bg-primary type-badge">Student</span>
                                        @else
                                            <span class="badge bg-secondary type-badge">Staff</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(in_array($reservation->status, ['pending', 'ready']))
                                            <span class="queue-badge">{{ $reservation->queue_position }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @switch($reservation->status)
                                            @case('pending')
                                                <span class="badge bg-primary status-badge">Pending</span>
                                                @break
                                            @case('ready')
                                                <span class="badge bg-success status-badge">Ready</span>
                                                @break
                                            @case('fulfilled')
                                                <span class="badge bg-secondary status-badge">Fulfilled</span>
                                                @break
                                            @case('expired')
                                                <span class="badge bg-warning text-dark status-badge">Expired</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger status-badge">Cancelled</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $reservation->created_at->format('d M Y') }}</td>
                                    <td>
                                        @if($reservation->status === 'ready' && $reservation->expires_at)
                                            <span class="{{ $isExpiredHold ? 'text-expired' : '' }}">
                                                {{ $reservation->expires_at->format('d M Y H:i') }}
                                            </span>
                                            @if($isExpiredHold)
                                                <i class="bx bx-error-circle text-danger" title="Past due"></i>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(in_array($reservation->status, ['pending', 'ready']))
                                            <button type="button"
                                                class="btn btn-danger btn-action btn-cancel-reservation"
                                                data-id="{{ $reservation->id }}"
                                                data-book="{{ $bookTitle }}"
                                                data-borrower="{{ $borrowerName }}"
                                                title="Cancel Reservation">
                                                <i class="bx bx-x"></i> Cancel
                                            </button>
                                        @else
                                            <span class="text-muted" style="font-size: 12px;">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $reservations->appends(request()->query())->links() }}
                </div>
            @endif

        </div>
    </div>

    {{-- Place Reservation Modal --}}
    <div class="modal fade" id="placeReservationModal" tabindex="-1" aria-labelledby="placeReservationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="placeReservationModalLabel">
                        <i class="bx bx-book-bookmark me-1"></i> Place Reservation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Book</label>
                        <div class="input-group">
                            <input type="text" id="reservation-book-search" class="form-control"
                                   placeholder="Search by title or ISBN..." autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary" id="reservation-book-search-btn">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>
                        <input type="hidden" id="reservation-book-id">
                        <div id="reservation-book-results" style="display:none;" class="mt-1"></div>
                        <div id="reservation-book-selected" style="display:none;" class="mt-1">
                            <span class="badge bg-info" id="reservation-book-label"></span>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-1" id="reservation-book-clear">
                                <i class="bx bx-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Borrower</label>
                        <div class="borrower-choices">
                            <select id="reservation-borrower-select" class="form-select">
                                <option value="">Search borrower by name or ID...</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="reservation-submit-btn" class="btn btn-primary btn-loading" disabled>
                        <span class="btn-text"><i class="bx bx-book-bookmark me-1"></i> Place Reservation</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Reserving...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            // ==================== CANCEL RESERVATION ====================
            document.querySelectorAll('.btn-cancel-reservation').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var reservationId = this.dataset.id;
                    var bookTitle = this.dataset.book;
                    var borrowerName = this.dataset.borrower;

                    Swal.fire({
                        title: 'Cancel Reservation?',
                        html: '<strong>' + borrowerName + '</strong> - "' + bookTitle + '"',
                        icon: 'warning',
                        input: 'text',
                        inputLabel: 'Reason (optional)',
                        inputPlaceholder: 'Enter cancellation reason...',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Cancel It',
                        cancelButtonText: 'Keep It',
                        confirmButtonColor: '#e74a3b',
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            fetch('/library/reservations/' + reservationId + '/cancel', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ reason: result.value || null }),
                            })
                            .then(function (r) { return r.json(); })
                            .then(function (data) {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Cancelled',
                                        text: data.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(function () {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', data.message || 'Failed to cancel reservation.', 'error');
                                }
                            })
                            .catch(function () {
                                Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                            });
                        }
                    });
                });
            });

            // ==================== BOOK SEARCH (AJAX) ====================
            var bookSearchInput = document.getElementById('reservation-book-search');
            var bookSearchBtn = document.getElementById('reservation-book-search-btn');
            var bookResultsDiv = document.getElementById('reservation-book-results');
            var bookIdInput = document.getElementById('reservation-book-id');
            var bookSelectedDiv = document.getElementById('reservation-book-selected');
            var bookLabel = document.getElementById('reservation-book-label');
            var bookClearBtn = document.getElementById('reservation-book-clear');
            var searchTimeout = null;

            function searchBooks() {
                var query = bookSearchInput.value.trim();
                if (query.length < 2) {
                    bookResultsDiv.style.display = 'none';
                    return;
                }

                $.ajax({
                    url: '{{ route("library.catalog.index") }}',
                    data: { search: query, format: 'json' },
                    success: function (response) {
                        var books = response.data || response;
                        if (!Array.isArray(books)) {
                            bookResultsDiv.style.display = 'none';
                            return;
                        }

                        var html = '<div class="list-group" style="max-height:200px; overflow-y:auto; font-size:13px;">';
                        books.slice(0, 10).forEach(function (book) {
                            html += '<a href="#" class="list-group-item list-group-item-action book-result-item" data-id="' + book.id + '" data-title="' + (book.title || '') + '">'
                                + (book.title || 'Untitled') + ' <small class="text-muted">(' + (book.isbn || 'No ISBN') + ')</small></a>';
                        });
                        if (books.length === 0) {
                            html += '<div class="list-group-item text-muted">No books found.</div>';
                        }
                        html += '</div>';
                        bookResultsDiv.innerHTML = html;
                        bookResultsDiv.style.display = 'block';

                        // Bind click events on results
                        document.querySelectorAll('.book-result-item').forEach(function (item) {
                            item.addEventListener('click', function (e) {
                                e.preventDefault();
                                bookIdInput.value = this.dataset.id;
                                bookLabel.textContent = this.dataset.title;
                                bookSelectedDiv.style.display = 'inline-block';
                                bookResultsDiv.style.display = 'none';
                                bookSearchInput.value = '';
                                checkSubmitReady();
                            });
                        });
                    }
                });
            }

            bookSearchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(searchBooks, 300);
            });

            bookSearchBtn.addEventListener('click', searchBooks);

            bookClearBtn.addEventListener('click', function () {
                bookIdInput.value = '';
                bookSelectedDiv.style.display = 'none';
                bookSearchInput.value = '';
                checkSubmitReady();
            });

            // ==================== BORROWER CHOICES.JS ====================
            var borrowerSelect = document.getElementById('reservation-borrower-select');
            var borrowerChoices = new Choices(borrowerSelect, {
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

            var borrowerSearchTimeout = null;
            borrowerChoices.input.element.addEventListener('input', function () {
                var searchVal = this.value.trim();
                if (searchVal.length < 2) return;

                clearTimeout(borrowerSearchTimeout);
                borrowerSearchTimeout = setTimeout(function () {
                    $.ajax({
                        url: '{{ route("library.borrowers.search") }}',
                        data: { q: searchVal },
                        success: function (results) {
                            var choices = results.map(function (item) {
                                return {
                                    value: item.type + ':' + item.id,
                                    label: item.name + ' -- ' + (item.type === 'student' ? 'Student' : 'Staff') + ' (' + (item.identifier || item.id) + ')'
                                };
                            });
                            borrowerChoices.setChoices(choices, 'value', 'label', true);
                        }
                    });
                }, 300);
            });

            borrowerSelect.addEventListener('change', function () {
                checkSubmitReady();
            });

            // ==================== SUBMIT RESERVATION ====================
            var submitBtn = document.getElementById('reservation-submit-btn');

            function checkSubmitReady() {
                var bookReady = bookIdInput.value !== '';
                var borrowerReady = borrowerSelect.value !== '';
                submitBtn.disabled = !(bookReady && borrowerReady);
            }

            submitBtn.addEventListener('click', function () {
                var bookId = bookIdInput.value;
                var borrowerVal = borrowerSelect.value;
                if (!bookId || !borrowerVal) return;

                var parts = borrowerVal.split(':');
                var borrowerType = parts[0];
                var borrowerId = parts[1];

                submitBtn.classList.add('loading');
                submitBtn.disabled = true;

                fetch('{{ route("library.reservations.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        book_id: bookId,
                        borrower_type: borrowerType,
                        borrower_id: borrowerId,
                    }),
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Reservation Placed',
                            text: data.message,
                            timer: 2500,
                            showConfirmButton: false
                        }).then(function () {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Cannot Reserve', data.message || 'An error occurred.', 'error');
                    }
                })
                .catch(function () {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                    Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                });
            });
        });
    </script>
@endsection

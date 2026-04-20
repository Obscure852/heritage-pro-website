@extends('layouts.master')
@section('title')
    Overdue Items
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

        /* Bracket Sections */
        .bracket-section {
            margin-bottom: 28px;
        }

        .bracket-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .bracket-header h6 {
            font-weight: 600;
            color: #1f2937;
            margin: 0;
            font-size: 15px;
        }

        .bracket-badge {
            font-size: 12px;
            padding: 2px 10px;
            border-radius: 12px;
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

        /* Table Tweaks */
        .table th {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-top: none;
        }

        .table td {
            font-size: 14px;
            vertical-align: middle;
        }

        .borrower-type-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .days-badge {
            font-size: 12px;
            padding: 3px 10px;
            border-radius: 10px;
            font-weight: 600;
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
            Overdue Items
        @endslot
    @endcomponent

    <div class="library-container">
        <div class="library-header">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <h3 style="margin:0;">Overdue Items</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        {{ $totalOverdue }} overdue {{ Str::plural('item', $totalOverdue) }} across all brackets
                    </p>
                </div>
                <div class="col-md-7">
                    <div class="row text-center">
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalOverdue }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['1-7 days'] ?? 0 }}</h4>
                                <small class="opacity-75">1-7 Days</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['8-14 days'] ?? 0 }}</h4>
                                <small class="opacity-75">8-14 Days</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['15-30 days'] ?? 0 }}</h4>
                                <small class="opacity-75">15-30 Days</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['30+ days'] ?? 0 }}</h4>
                                <small class="opacity-75">30+ Days</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="library-body">
            @if($totalOverdue > 0)
                {{-- Bracket Sections --}}
                @php
                    $bracketConfig = [
                        '1-7 days' => ['color' => 'info', 'icon' => 'bx-time-five'],
                        '8-14 days' => ['color' => 'warning', 'icon' => 'bx-error'],
                        '15-30 days' => ['color' => 'secondary', 'icon' => 'bx-error-alt'],
                        '30+ days' => ['color' => 'danger', 'icon' => 'bx-x-circle'],
                    ];
                @endphp

                @foreach($brackets as $label => $items)
                    <div class="bracket-section">
                        <div class="bracket-header">
                            <i class="bx {{ $bracketConfig[$label]['icon'] ?? 'bx-time' }}" style="font-size: 1.2rem; color: var(--bs-{{ $bracketConfig[$label]['color'] ?? 'secondary' }});"></i>
                            <h6>{{ $label }}</h6>
                            <span class="badge bg-{{ $bracketConfig[$label]['color'] ?? 'secondary' }} bracket-badge">
                                {{ $items->count() }} {{ Str::plural('item', $items->count()) }}
                            </span>
                        </div>

                        @if($items->isEmpty())
                            <p class="text-muted ms-4 mb-0" style="font-size: 14px;">No items in this bracket.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Accession No</th>
                                            <th>Book Title</th>
                                            <th>Borrower</th>
                                            <th>Type</th>
                                            <th>Due Date</th>
                                            <th>Days Overdue</th>
                                            <th>Checked Out By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $transaction)
                                            <tr>
                                                <td>
                                                    <code>{{ $transaction->copy->accession_number ?? '-' }}</code>
                                                </td>
                                                <td>{{ $transaction->copy->book->title ?? '-' }}</td>
                                                <td>
                                                    {{ $transaction->borrower->full_name ?? $transaction->borrower->name ?? '-' }}
                                                </td>
                                                <td>
                                                    @if($transaction->borrower_type === 'student')
                                                        <span class="badge bg-primary borrower-type-badge">Student</span>
                                                    @else
                                                        <span class="badge bg-secondary borrower-type-badge">Staff</span>
                                                    @endif
                                                </td>
                                                <td>{{ $transaction->due_date->format('d M Y') }}</td>
                                                <td>
                                                    @if($transaction->days_overdue > 30)
                                                        <span class="badge bg-danger days-badge">{{ $transaction->days_overdue }} days</span>
                                                    @elseif($transaction->days_overdue >= 15)
                                                        <span class="badge bg-warning text-dark days-badge">{{ $transaction->days_overdue }} days</span>
                                                    @else
                                                        <span class="badge bg-info days-badge">{{ $transaction->days_overdue }} days</span>
                                                    @endif
                                                </td>
                                                <td>{{ $transaction->checkedOutBy->name ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                {{-- Empty State --}}
                <div class="empty-state">
                    <i class="bx bx-check-circle d-block"></i>
                    <h5>No Overdue Items</h5>
                    <p>All books are returned on time! There are currently no overdue items to display.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

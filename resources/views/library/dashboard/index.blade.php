@extends('layouts.master')
@section('title')
    Library Dashboard
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
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        /* Data cards */
        .data-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .data-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .data-card-header h5 {
            margin: 0;
            font-weight: 600;
            color: #374151;
        }

        .data-card-body {
            padding: 0;
        }

        .view-all-link {
            font-size: 13px;
            color: #3b82f6;
            text-decoration: none;
        }

        .view-all-link:hover {
            text-decoration: underline;
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

        /* Info line */
        .info-line {
            font-size: 13px;
            color: #6b7280;
            padding: 8px 0 16px 0;
        }

        .info-line i {
            margin-right: 4px;
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

        /* Activity Feed */
        .activity-item {
            padding: 12px 20px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            transition: background-color 0.15s;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item:hover {
            background-color: #f9fafb;
        }

        .activity-icon .badge {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .activity-content {
            flex: 1;
            min-width: 0;
        }

        .activity-label {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .activity-meta {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 2px;
        }

        .activity-notes {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
            font-style: italic;
        }

        /* Overdue bracket badges */
        .overdue-total {
            font-size: 2.5rem;
            font-weight: 700;
            color: #e74a3b;
            line-height: 1;
        }

        .overdue-total-label {
            font-size: 0.85rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        .bracket-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
        }

        .bracket-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #374151;
        }

        .bracket-count {
            font-weight: 700;
            min-width: 20px;
            text-align: center;
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .quick-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid #e5e7eb;
            background: white;
            color: #374151;
        }

        .quick-action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            color: #1f2937;
            text-decoration: none;
        }

        .quick-action-btn i {
            font-size: 16px;
        }

        .quick-action-btn .action-count {
            background: #ef4444;
            color: white;
            font-size: 11px;
            font-weight: 600;
            padding: 1px 7px;
            border-radius: 10px;
            min-width: 20px;
            text-align: center;
            line-height: 1.5;
        }

        .quick-action-btn .action-count.count-warning {
            background: #f59e0b;
        }

        /* Reports Dropdown -- matches students/index.blade.php pattern */
        .reports-dropdown .dropdown-toggle {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
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
            min-width: 280px;
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

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 0.95rem;
            }

            .stat-item small {
                font-size: 0.6rem;
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
            Dashboard
        @endslot
    @endcomponent

    <div class="library-container">
        <div class="library-header">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <h3 style="margin:0; font-size: 1.25rem;">Library Dashboard</h3>
                    <p style="margin:4px 0 0 0; opacity:.9; font-size: 12px;">
                        {{ now()->format('l, d F Y') }}
                    </p>
                </div>
                <div class="col-md-9">
                    <div class="row text-center">
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" style="font-size: 1.1rem;">{{ number_format($collectionSummary['total_books']) }}</h4>
                                <small class="opacity-75" style="font-size: 0.7rem;">Total Items</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" style="font-size: 1.1rem;">{{ number_format($collectionSummary['total_copies']) }}</h4>
                                <small class="opacity-75" style="font-size: 0.7rem;">Total Copies</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" style="font-size: 1.1rem;">{{ number_format($collectionSummary['available']) }}</h4>
                                <small class="opacity-75" style="font-size: 0.7rem;">Available</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" style="font-size: 1.1rem;">{{ number_format($collectionSummary['checked_out']) }}</h4>
                                <small class="opacity-75" style="font-size: 0.7rem;">Checked Out</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white" style="font-size: 1.1rem;">{{ number_format($overdueData['total']) }}</h4>
                                <small class="opacity-75" style="font-size: 0.7rem;">Overdue</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="library-body">
            <div class="help-text">
                <div class="help-title">Daily Overview</div>
                <div class="help-content">Your library activity summary for today. Statistics update on page refresh.</div>
            </div>

            {{-- Quick Actions --}}
            <div class="quick-actions">
                <a href="{{ route('library.catalog.index') }}" class="quick-action-btn">
                    <i class="bx bx-book-open" style="color: #3b82f6;"></i> Catalog
                </a>
                <a href="{{ route('library.books.create') }}" class="quick-action-btn">
                    <i class="bx bx-plus-circle" style="color: #10b981;"></i> Add Item
                </a>
                <a href="{{ route('library.borrowers.index') }}" class="quick-action-btn">
                    <i class="bx bx-group" style="color: #6366f1;"></i> Borrowers
                </a>
                <a href="{{ route('library.circulation.index') }}" class="quick-action-btn">
                    <i class="bx bx-transfer" style="color: #0891b2;"></i> Circulation
                </a>
                <a href="{{ route('library.overdue.index') }}" class="quick-action-btn">
                    <i class="bx bx-error-circle" style="color: #ef4444;"></i> Overdue Items
                    @if($overdueData['total'] > 0)
                        <span class="action-count">{{ $overdueData['total'] }}</span>
                    @endif
                </a>
                <a href="{{ route('library.fines.index') }}" class="quick-action-btn">
                    <i class="bx bx-dollar-circle" style="color: #f59e0b;"></i> Fines
                    @if($unpaidFinesCount > 0)
                        <span class="action-count count-warning">{{ $unpaidFinesCount }}</span>
                    @endif
                </a>

                {{-- Reports Dropdown --}}
                <div class="btn-group reports-dropdown" style="margin-left: auto;">
                    <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2" style="font-size: 10px;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @if(Route::has('library.reports.circulation'))
                            <li><a class="dropdown-item" href="{{ route('library.reports.circulation') }}">
                                <i class="fas fa-exchange-alt me-2" style="color: #4287f5;"></i> Circulation Report</a>
                            </li>
                        @endif
                        @if(Route::has('library.reports.overdue'))
                            <li><a class="dropdown-item" href="{{ route('library.reports.overdue') }}">
                                <i class="fas fa-exclamation-circle me-2" style="color: #dc3545;"></i> Overdue Report</a>
                            </li>
                        @endif
                        @if(Route::has('library.reports.most-borrowed'))
                            <li><a class="dropdown-item" href="{{ route('library.reports.most-borrowed') }}">
                                <i class="fas fa-chart-line me-2" style="color: #4287f5;"></i> Most Borrowed Items</a>
                            </li>
                        @endif
                        @if(Route::has('library.reports.borrower-activity'))
                            <li><a class="dropdown-item" href="{{ route('library.reports.borrower-activity') }}">
                                <i class="fas fa-users me-2" style="color: #6a5acd;"></i> Borrower Activity</a>
                            </li>
                        @endif
                        @if(Route::has('library.reports.collection-development'))
                            <li><a class="dropdown-item" href="{{ route('library.reports.collection-development') }}">
                                <i class="fas fa-layer-group me-2" style="color: #4287f5;"></i> Collection Development</a>
                            </li>
                        @endif
                        @if(Route::has('library.reports.fine-collection'))
                            <li><a class="dropdown-item" href="{{ route('library.reports.fine-collection') }}">
                                <i class="fas fa-dollar-sign me-2" style="color: #d97706;"></i> Fine Collection</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            {{-- Lost/On-hold/Missing info line --}}
            @if(($collectionSummary['lost'] ?? 0) > 0 || ($collectionSummary['on_hold'] ?? 0) > 0 || ($collectionSummary['missing'] ?? 0) > 0)
                <div class="info-line">
                    @if(($collectionSummary['lost'] ?? 0) > 0)
                        <i class="bx bx-error-circle text-warning"></i>
                        {{ $collectionSummary['lost'] }} {{ Str::plural('copy', $collectionSummary['lost']) }} marked as lost
                    @endif
                    @if(($collectionSummary['lost'] ?? 0) > 0 && (($collectionSummary['on_hold'] ?? 0) > 0 || ($collectionSummary['missing'] ?? 0) > 0))
                        &middot;
                    @endif
                    @if(($collectionSummary['on_hold'] ?? 0) > 0)
                        <i class="bx bx-time-five text-info"></i>
                        {{ $collectionSummary['on_hold'] }} {{ Str::plural('copy', $collectionSummary['on_hold']) }} on hold
                    @endif
                    @if(($collectionSummary['on_hold'] ?? 0) > 0 && ($collectionSummary['missing'] ?? 0) > 0)
                        &middot;
                    @endif
                    @if(($collectionSummary['missing'] ?? 0) > 0)
                        <i class="bx bx-search-alt text-secondary"></i>
                        {{ $collectionSummary['missing'] }} {{ Str::plural('copy', $collectionSummary['missing']) }} missing
                    @endif
                </div>
            @endif

            {{-- Due Today Section --}}
            <div class="data-card">
                <div class="data-card-header">
                    <h5><i class="bx bx-calendar me-2"></i>Due Today</h5>
                    @if(Route::has('library.circulation.index'))
                        <a href="{{ route('library.circulation.index') }}" class="view-all-link">Go to Circulation</a>
                    @endif
                </div>
                <div class="data-card-body">
                    @if($dueToday->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Accession No</th>
                                        <th>Title</th>
                                        <th>Borrower</th>
                                        <th>Type</th>
                                        <th>Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dueToday as $transaction)
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
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="bx bx-calendar-check d-block"></i>
                            <h5>No Items Due Today</h5>
                            <p>There are no items due for return today.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Two-Column Row: Overdue Summary + Popular Items --}}
            <div class="row mb-4">
                {{-- Overdue Summary Card --}}
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="data-card" style="height: 100%;">
                        <div class="data-card-header">
                            <h5><i class="bx bx-error-circle me-2"></i>Overdue Items</h5>
                            <a href="{{ route('library.overdue.index') }}" class="view-all-link">View All</a>
                        </div>
                        <div class="data-card-body" style="padding: 20px;">
                            @if($overdueData['total'] > 0)
                                <div class="text-center mb-3">
                                    <div class="overdue-total">{{ $overdueData['total'] }}</div>
                                    <div class="overdue-total-label">total overdue {{ Str::plural('item', $overdueData['total']) }}</div>
                                </div>
                                <div class="bracket-list justify-content-center">
                                    @php
                                        $bracketColors = [
                                            '1-7 days' => 'info',
                                            '8-14 days' => 'warning',
                                            '15-30 days' => 'secondary',
                                            '30+ days' => 'danger',
                                        ];
                                    @endphp
                                    @foreach($overdueData['summary'] as $bracket => $count)
                                        <div class="bracket-item">
                                            <span class="badge bg-{{ $bracketColors[$bracket] ?? 'secondary' }}">
                                                <span class="bracket-count">{{ $count }}</span>
                                            </span>
                                            <span>{{ $bracket }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-state" style="padding: 32px 24px;">
                                    <i class="bx bx-check-circle d-block" style="color: #1cc88a;"></i>
                                    <h5>No Overdue Items</h5>
                                    <p>All books are returned on time!</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Popular Items Card --}}
                <div class="col-lg-6">
                    <div class="data-card" style="height: 100%;">
                        <div class="data-card-header">
                            <h5><i class="bx bx-trending-up me-2"></i>Popular Items <small class="text-muted fw-normal ms-2">This Term</small></h5>
                        </div>
                        <div class="data-card-body">
                            @if($popularBooks->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px;">#</th>
                                                <th>Title</th>
                                                <th class="text-end" style="width: 100px;">Checkouts</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($popularBooks as $book)
                                                <tr>
                                                    <td class="text-muted">{{ $loop->iteration }}</td>
                                                    <td>{{ $book->title }}</td>
                                                    <td class="text-end fw-bold">{{ $book->checkout_count }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="empty-state" style="padding: 32px 24px;">
                                    <i class="bx bx-book-open d-block" style="color: #6b7280;"></i>
                                    <h5>No Checkouts Yet</h5>
                                    <p>Popular books will appear once circulation begins.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Activity Feed --}}
            <div class="data-card">
                <div class="data-card-header">
                    <h5><i class="bx bx-pulse me-2"></i>Recent Activity <small class="text-muted fw-normal ms-2">Last 20</small></h5>
                </div>
                <div class="data-card-body">
                    @if($recentActivity->count() > 0)
                        <div style="max-height: 400px; overflow-y: auto;">
                            @php
                                $activityIcons = [
                                    'checkout' => ['color' => 'primary', 'icon' => 'bx-log-out-circle'],
                                    'checkin' => ['color' => 'success', 'icon' => 'bx-log-in-circle'],
                                    'renewal' => ['color' => 'info', 'icon' => 'bx-refresh'],
                                    'fine_assessed' => ['color' => 'danger', 'icon' => 'bx-dollar'],
                                    'lost_fine_assessed' => ['color' => 'danger', 'icon' => 'bx-dollar'],
                                    'fine_payment' => ['color' => 'success', 'icon' => 'bx-dollar'],
                                    'fine_waiver' => ['color' => 'warning', 'icon' => 'bx-gift'],
                                    'reservation_placed' => ['color' => 'info', 'icon' => 'bx-bookmark'],
                                    'reservation_fulfilled' => ['color' => 'info', 'icon' => 'bx-bookmark'],
                                ];
                            @endphp
                            @foreach($recentActivity as $activity)
                                @php
                                    $iconConfig = $activityIcons[$activity->action] ?? ['color' => 'secondary', 'icon' => 'bx-dots-horizontal-rounded'];
                                @endphp
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <span class="badge rounded-circle bg-{{ $iconConfig['color'] }}">
                                            <i class="bx {{ $iconConfig['icon'] }}"></i>
                                        </span>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-label">
                                            {{ \App\Services\Library\DashboardService::actionLabel($activity->action) }}
                                        </div>
                                        <div class="activity-meta">
                                            {{ $activity->user->name ?? 'System' }}
                                            &middot;
                                            {{ $activity->created_at->diffForHumans() }}
                                        </div>
                                        @if($activity->notes)
                                            <div class="activity-notes">{{ $activity->notes }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="bx bx-pulse d-block" style="color: #6b7280;"></i>
                            <h5>No Recent Activity</h5>
                            <p>Activity will appear as library operations are performed.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

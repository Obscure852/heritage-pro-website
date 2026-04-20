@extends('layouts.master')
@section('title')
    Borrower Activity Report
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

        .empty-state {
            text-align: center;
            padding: 48px 24px;
        }

        .empty-state i {
            font-size: 3rem;
            color: #6b7280;
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

        /* Filter Section */
        .filter-section {
            background: #f8f9fa;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 24px;
            border: 1px solid #e5e7eb;
        }

        /* Export Button */
        .btn-export {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-export:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        /* Summary Stats */
        .summary-stats {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .summary-stat {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px 20px;
            flex: 1;
            min-width: 150px;
            text-align: center;
        }

        .summary-stat .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }

        .summary-stat .stat-desc {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Borrower Type Badges */
        .borrower-type-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .borrower-type-badge.student {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .borrower-type-badge.staff {
            background-color: #fef3c7;
            color: #92400e;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #3b82f6;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            margin-bottom: 16px;
        }

        .back-link:hover {
            color: #2563eb;
            text-decoration: underline;
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

            .summary-stats {
                flex-direction: column;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('library.dashboard') }}">Library</a>
        @endslot
        @slot('title') Borrower Activity Report @endslot
    @endcomponent

    <div class="library-container">
        <div class="library-header">
            <div class="row align-items-center">
                <div class="col-md-5">
                    @if($mode === 'individual')
                        <h3 style="margin:0;">Borrower Activity: {{ $individualData['borrower_name'] }}</h3>
                        <p style="margin:6px 0 0 0; opacity:.9;">
                            {{ $individualData['borrower_type'] }} &middot; {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
                        </p>
                    @else
                        <h3 style="margin:0;">Borrower Activity Report</h3>
                        <p style="margin:6px 0 0 0; opacity:.9;">
                            Aggregate summary for {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
                        </p>
                    @endif
                </div>
                <div class="col-md-7">
                    <div class="row text-center">
                        @if($mode === 'individual')
                            <div class="col">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $individualData['summary']['total_checkouts'] }}</h4>
                                    <small class="opacity-75">Total Checkouts</small>
                                </div>
                            </div>
                            <div class="col">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $individualData['summary']['active_loans'] }}</h4>
                                    <small class="opacity-75">Active Loans</small>
                                </div>
                            </div>
                        @else
                            <div class="col">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $records->count() }}</h4>
                                    <small class="opacity-75">Total Borrowers</small>
                                </div>
                            </div>
                            <div class="col">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $records->sum('total_checkouts') }}</h4>
                                    <small class="opacity-75">Total Checkouts</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="library-body">
            @if($mode === 'individual')
                {{-- Back to Aggregate Link --}}
                <a href="{{ route('library.reports.borrower-activity', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Aggregate View
                </a>

                {{-- Individual Summary Stats --}}
                <div class="summary-stats">
                    <div class="summary-stat">
                        <div class="stat-number">{{ $individualData['summary']['total_checkouts'] }}</div>
                        <div class="stat-desc">Total Checkouts</div>
                    </div>
                    <div class="summary-stat">
                        <div class="stat-number">{{ $individualData['summary']['total_returns'] }}</div>
                        <div class="stat-desc">Returns</div>
                    </div>
                    <div class="summary-stat">
                        <div class="stat-number">{{ $individualData['summary']['active_loans'] }}</div>
                        <div class="stat-desc">Active Loans</div>
                    </div>
                    <div class="summary-stat">
                        <div class="stat-number">P {{ $individualData['summary']['total_fines'] }}</div>
                        <div class="stat-desc">Total Fines</div>
                    </div>
                </div>

                {{-- Individual Detail Table --}}
                @if($individualData['records']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>Checkout Date</th>
                                    <th>Return Date</th>
                                    <th>Book Title</th>
                                    <th>Accession No</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Fine Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($individualData['records'] as $record)
                                    <tr>
                                        <td class="text-muted">{{ $loop->iteration }}</td>
                                        <td>{{ $record['checkout_date'] }}</td>
                                        <td>{{ $record['return_date'] }}</td>
                                        <td>{{ $record['book_title'] }}</td>
                                        <td><code>{{ $record['accession_number'] }}</code></td>
                                        <td>{{ $record['status'] }}</td>
                                        <td style="text-align: right;">{{ $record['fine_amount'] !== '0.00' ? 'P ' . $record['fine_amount'] : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bx bx-user d-block"></i>
                        <h5>No Loans Found</h5>
                        <p>This borrower has no loans during the selected period.</p>
                    </div>
                @endif
            @else
                {{-- Filter Section (Aggregate Mode) --}}
                <div class="filter-section">
                    <form method="GET" action="{{ route('library.reports.borrower-activity') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label" style="font-size: 13px; font-weight: 600; color: #374151;">Start Date</label>
                                <input type="date" class="form-control form-control-sm" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" style="font-size: 13px; font-weight: 600; color: #374151;">End Date</label>
                                <input type="date" class="form-control form-control-sm" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" style="font-size: 13px; font-weight: 600; color: #374151;">Borrower Type</label>
                                <select class="form-select form-select-sm" name="borrower_type">
                                    <option value="">All Types</option>
                                    <option value="student" {{ ($filters['borrower_type'] ?? '') === 'student' ? 'selected' : '' }}>Student</option>
                                    <option value="user" {{ ($filters['borrower_type'] ?? '') === 'user' ? 'selected' : '' }}>Staff</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" style="font-size: 13px; font-weight: 600; color: #374151;">Grade</label>
                                <select class="form-select form-select-sm" name="grade_id" id="grade_id">
                                    <option value="">All Grades</option>
                                    @foreach($grades as $grade)
                                        <option value="{{ $grade->id }}" {{ ($filters['grade_id'] ?? '') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" style="font-size: 13px; font-weight: 600; color: #374151;">Class</label>
                                <select class="form-select form-select-sm" name="klass_id" id="klass_id">
                                    <option value="">All Classes</option>
                                    @foreach($klasses as $klass)
                                        <option value="{{ $klass->id }}" data-grade="{{ $klass->grade_id }}" {{ ($filters['klass_id'] ?? '') == $klass->id ? 'selected' : '' }}>{{ $klass->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100 mb-1"><i class="fas fa-filter"></i> Filter</button>
                                <a href="{{ route('library.reports.borrower-activity.export', request()->query()) }}" class="btn-export w-100 text-center"><i class="fas fa-file-excel"></i> Export</a>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Aggregate Summary Stats --}}
                <div class="summary-stats">
                    <div class="summary-stat">
                        <div class="stat-number">{{ $records->count() }}</div>
                        <div class="stat-desc">Total Borrowers</div>
                    </div>
                    <div class="summary-stat">
                        <div class="stat-number">{{ $records->sum('total_checkouts') }}</div>
                        <div class="stat-desc">Total Checkouts</div>
                    </div>
                    <div class="summary-stat">
                        <div class="stat-number">{{ $records->sum('overdue_count') }}</div>
                        <div class="stat-desc">Currently Overdue</div>
                    </div>
                </div>

                {{-- Aggregate Data Table --}}
                @if($records->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>Borrower</th>
                                    <th>Type</th>
                                    <th style="text-align: right;">Total Checkouts</th>
                                    <th style="text-align: right;">Returns</th>
                                    <th style="text-align: right;">Active Loans</th>
                                    <th style="text-align: right;">Overdue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $record)
                                    <tr>
                                        <td class="text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ route('library.reports.borrower-activity', array_merge(request()->query(), ['borrower_type' => $record['borrower_type_raw'], 'borrower_id' => $record['borrower_id']])) }}">
                                                {{ $record['borrower_name'] }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="borrower-type-badge {{ $record['borrower_type'] === 'Student' ? 'student' : 'staff' }}">
                                                {{ $record['borrower_type'] }}
                                            </span>
                                        </td>
                                        <td style="text-align: right; font-weight: 600;">{{ $record['total_checkouts'] }}</td>
                                        <td style="text-align: right;">{{ $record['total_returns'] }}</td>
                                        <td style="text-align: right;">{{ $record['currently_active'] }}</td>
                                        <td style="text-align: right;">
                                            @if($record['overdue_count'] > 0)
                                                <span style="color: #e74a3b; font-weight: 600;">{{ $record['overdue_count'] }}</span>
                                            @else
                                                {{ $record['overdue_count'] }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bx bx-user d-block"></i>
                        <h5>No Borrower Activity</h5>
                        <p>No borrower activity found for this period.</p>
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.getElementById('grade_id')?.addEventListener('change', function() {
            const gradeId = this.value;
            const klassSelect = document.getElementById('klass_id');
            Array.from(klassSelect.options).forEach(opt => {
                if (!opt.value) return; // keep "All Classes"
                opt.style.display = (!gradeId || opt.dataset.grade === gradeId) ? '' : 'none';
            });
            klassSelect.value = '';
        });
    </script>
@endsection

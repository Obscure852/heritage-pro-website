@extends('layouts.master')
@section('title')
    Fee Structures
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

        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .action-buttons .btn i {
            font-size: 16px;
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

        .amount-cell {
            font-weight: 600;
            color: #059669;
        }

        .locked-row {
            background-color: #f9fafb;
        }

        .locked-icon {
            color: #9ca3af;
            margin-right: 4px;
        }

        .grade-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #e0e7ff;
            color: #3730a3;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

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

        .year-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #d1fae5;
            color: #065f46;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .year-badge.historical {
            background: #f3f4f6;
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .fee-header {
                padding: 20px;
            }

            .fee-body {
                padding: 16px;
            }

            .header-buttons {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="javascript:history.back()">Back</a>
        @endslot
        @slot('title')
            Fee Administration
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
                    <h3 style="margin:0;">Fee Structures</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage fee amounts by grade and year</p>
                </div>
                <div class="col-md-6">
                    @php
                        $totalAmount = $feeStructures->sum('amount');
                        $currentYear = date('Y');
                        $currentYearCount = $feeStructures->where('year', $currentYear)->count();
                    @endphp
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $feeStructures->count() }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $currentYearCount }}</h4>
                                <small class="opacity-75">{{ $currentYear }}</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ format_currency($totalAmount, 0) }}</h4>
                                <small class="opacity-75">Total Value</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fee-body">
            <div class="help-text">
                <div class="help-title">Fee Structures Directory</div>
                <div class="help-content">
                    Fee structures define the specific amounts charged for each fee type, grade, and year.
                    Historical years (past calendar years) are locked and cannot be modified.
                    Use the "Copy Structures" feature to quickly create fee structures for a new year based on existing ones.
                </div>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <select class="form-select" id="gradeFilter">
                                    <option value="">All Grades</option>
                                    @foreach ($grades ?? [] as $grade)
                                        <option value="{{ strtolower($grade->name) }}">{{ $grade->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <select class="form-select" id="yearFilter">
                                    <option value="">All Years</option>
                                    @php
                                        $years = $feeStructures->pluck('year')->unique()->sort()->reverse();
                                    @endphp
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}" {{ $year == ($currentTermYear ?? '') ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search fee type..." id="searchInput">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <button type="button" class="btn btn-light w-100" id="resetFilters">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 header-buttons">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#copyStructuresModal">
                            <i class="fas fa-copy me-1"></i> Copy Structures
                        </button>
                        <a href="{{ route('fees.setup.structures.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Add Fee Structure
                        </a>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="feeStructuresTable" class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fee Type</th>
                            <th>Grade</th>
                            <th>Year</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($feeStructures as $index => $structure)
                            @php
                                $isHistorical = $structure->year < date('Y');
                            @endphp
                            <tr class="fee-structure-row {{ $isHistorical ? 'locked-row' : '' }}"
                                data-fee-type="{{ strtolower($structure->feeType->name ?? '') }}"
                                data-grade="{{ strtolower($structure->grade->name ?? '') }}"
                                data-year="{{ $structure->year }}">
                                <td>
                                    @if ($isHistorical)
                                        <i class="fas fa-lock locked-icon" title="Historical year - locked"></i>
                                    @endif
                                    {{ $index + 1 }}
                                </td>
                                <td>{{ $structure->feeType->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="grade-badge">{{ $structure->grade->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="year-badge {{ $isHistorical ? 'historical' : '' }}">
                                        @if ($isHistorical)
                                            <i class="fas fa-lock" style="font-size: 10px;"></i>
                                        @endif
                                        {{ $structure->year }}
                                    </span>
                                </td>
                                <td class="text-end amount-cell">{{ format_currency($structure->amount) }}</td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <a href="{{ route('fees.setup.structures.edit', $structure->id) }}"
                                            class="btn btn-sm btn-outline-info {{ $isHistorical ? 'disabled' : '' }}"
                                            title="{{ $isHistorical ? 'Historical year - cannot edit' : 'Edit Fee Structure' }}"
                                            {{ $isHistorical ? 'aria-disabled=true tabindex=-1' : '' }}>
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                        <form action="{{ route('fees.setup.structures.destroy', $structure->id) }}"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return {{ $isHistorical ? 'false' : 'confirmDelete()' }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="{{ $isHistorical ? 'Historical year - cannot delete' : 'Delete Fee Structure' }}"
                                                {{ $isHistorical ? 'disabled' : '' }}>
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="no-fee-structures-row">
                                <td colspan="6">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="fas fa-layer-group" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0" style="font-size: 15px;">No Fee Structures</p>
                                        <p class="text-muted" style="font-size: 13px;">Create your first fee structure to get started</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('fees.setup.fee-structures._copy-modal')
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeFilters();
            initializeAlertDismissal();
        });

        function initializeFilters() {
            const searchInput = document.getElementById('searchInput');
            const gradeFilter = document.getElementById('gradeFilter');
            const yearFilter = document.getElementById('yearFilter');
            const resetBtn = document.getElementById('resetFilters');

            function filterRows() {
                const searchTerm = searchInput.value.toLowerCase();
                const gradeValue = gradeFilter.value.toLowerCase();
                const yearValue = yearFilter.value;

                const rows = document.querySelectorAll('.fee-structure-row');

                rows.forEach(row => {
                    const feeType = row.dataset.feeType || '';
                    const grade = row.dataset.grade || '';
                    const year = row.dataset.year || '';

                    const matchesSearch = !searchTerm || feeType.includes(searchTerm);
                    const matchesGrade = !gradeValue || grade === gradeValue;
                    const matchesYear = !yearValue || year === yearValue;

                    row.style.display = (matchesSearch && matchesGrade && matchesYear) ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterRows);
            gradeFilter.addEventListener('change', filterRows);
            yearFilter.addEventListener('change', filterRows);

            resetBtn.addEventListener('click', function() {
                searchInput.value = '';
                gradeFilter.value = '';
                yearFilter.value = '';
                filterRows();
            });

            // Apply filter on page load (for pre-selected year)
            filterRows();
        }

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

        function confirmDelete() {
            return confirm('Are you sure you want to delete this fee structure? This action cannot be undone.');
        }
    </script>
@endsection

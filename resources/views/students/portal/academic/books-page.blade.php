@extends('layouts.master-student-portal')
@section('title')
    My Books
@endsection

@section('css')
<style>
    .portal-container {
        background: white;
        border-radius: 3px;
        padding: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .portal-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 28px;
        border-radius: 3px 3px 0 0;
    }

    .portal-body {
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

    .grade-filter {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .grade-btn {
        padding: 8px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        background: white;
        color: #374151;
        font-weight: 500;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .grade-btn:hover {
        border-color: #3b82f6;
        color: #3b82f6;
    }

    .grade-btn.active {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        border-color: transparent;
        color: white;
    }

    .book-card {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 16px;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }

    .book-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
    }

    .book-card.returned {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .book-title {
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }

    .book-author {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .book-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        font-size: 12px;
        color: #6b7280;
    }

    .book-meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .book-meta-item i {
        font-size: 14px;
    }

    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .status-borrowed {
        background: #fef3c7;
        color: #92400e;
    }

    .status-returned {
        background: #d1fae5;
        color: #065f46;
    }

    .accession-number {
        font-family: monospace;
        background: #f3f4f6;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 12px;
    }

    .table-books {
        width: 100%;
        border-collapse: collapse;
    }

    .table-books th {
        background: #f8fafc;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        border-bottom: 2px solid #e5e7eb;
    }

    .table-books td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .table-books tbody tr:hover {
        background: #f9fafb;
    }

    .check-icon {
        color: #10b981;
        font-size: 18px;
    }

    @media (max-width: 768px) {
        .portal-header {
            padding: 20px;
        }

        .portal-body {
            padding: 16px;
        }

        .table-books {
            display: block;
            overflow-x: auto;
        }
    }
</style>
@endsection

@section('content')
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

    <div class="portal-container">
        <div class="portal-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin:0;">
                        <i class="bx bx-book me-2"></i> My Books
                    </h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        {{ $student->currentClass?->name ?? 'No Class Assigned' }} -
                        {{ $student->first_name }} {{ $student->last_name }}
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-md-0 mt-3">
                    <span class="badge bg-light text-dark">
                        {{ $bookAllocations->count() }} Book{{ $bookAllocations->count() != 1 ? 's' : '' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="portal-body">
            <div class="help-text">
                <div class="help-title">Textbook Allocations</div>
                <div class="help-content">
                    View your allocated textbooks. Select a grade to filter books by grade level.
                    Returned books are marked with a green checkmark.
                </div>
            </div>

            @if($grades->count() > 0)
                <!-- Grade Filter -->
                <div class="grade-filter">
                    <span class="text-muted me-2" style="line-height: 36px; font-size: 13px;">Filter by Grade:</span>
                    @foreach($grades as $grade)
                        <a href="{{ route('student.academic.books', ['grade_id' => $grade->id]) }}"
                           class="grade-btn {{ $selectedGradeId == $grade->id ? 'active' : '' }}">
                            {{ $grade->name }}
                        </a>
                    @endforeach
                </div>

                @if($bookAllocations->count() > 0)
                    <div class="table-responsive">
                        <table class="table-books">
                            <thead>
                                <tr>
                                    <th>Book Title</th>
                                    <th>Author</th>
                                    <th>Accession #</th>
                                    <th>Allocated</th>
                                    <th>Returned</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bookAllocations as $allocation)
                                    <tr>
                                        <td>
                                            <div class="book-title">{{ $allocation->copy->book->title ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $allocation->copy->book->author->name ?? 'Unknown' }}</span>
                                        </td>
                                        <td>
                                            <span class="accession-number">{{ $allocation->accession_number ?? $allocation->copy->accession_number ?? '-' }}</span>
                                        </td>
                                        <td>
                                            @if($allocation->allocation_date)
                                                {{ $allocation->allocation_date->format('d M Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($allocation->return_date)
                                                {{ $allocation->return_date->format('d M Y') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($allocation->return_date)
                                                <span class="status-badge status-returned">
                                                    <i class="bx bx-check-circle"></i> Returned
                                                </span>
                                            @else
                                                <span class="status-badge status-borrowed">
                                                    <i class="bx bx-book-open"></i> Borrowed
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bx bx-book text-muted display-4"></i>
                        <p class="text-muted mt-3">No books allocated for this grade</p>
                        <p class="text-muted small">Select a different grade to view other allocations.</p>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="bx bx-book text-muted display-4"></i>
                    <p class="text-muted mt-3">No textbooks have been allocated to you yet</p>
                    <p class="text-muted small">Your allocated textbooks will appear here.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

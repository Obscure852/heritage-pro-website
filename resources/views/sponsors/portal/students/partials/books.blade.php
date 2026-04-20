{{-- Books Allocation Partial --}}
<div class="help-text mb-4">
    <div class="help-title">Book Allocations</div>
    <div class="help-content">
        View all books allocated to your child. Please ensure books are returned in good condition by the due date.
    </div>
</div>

@if($student->bookAllocations && $student->bookAllocations->count() > 0)
    <div class="subject-table-container">
        <table class="table table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Accession #</th>
                    <th>Allocated</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Condition (Issue)</th>
                    <th>Condition (Return)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($student->bookAllocations as $allocation)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="subject-icon" style="width: 32px; height: 32px; font-size: 14px;">
                                    <i class="bx bx-book-open"></i>
                                </span>
                                <span class="fw-medium">{{ $allocation->book->title ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>{{ $allocation->accession_number ?? 'N/A' }}</td>
                        <td>{{ $allocation->allocation_date?->format('d M Y') ?? 'N/A' }}</td>
                        <td>
                            @if($allocation->due_date)
                                @php
                                    $isOverdue = !$allocation->return_date && $allocation->due_date->isPast();
                                @endphp
                                <span class="{{ $isOverdue ? 'text-danger fw-medium' : '' }}">
                                    {{ $allocation->due_date->format('d M Y') }}
                                    @if($isOverdue)
                                        <i class="bx bx-error-circle ms-1"></i>
                                    @endif
                                </span>
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($allocation->return_date)
                                <span class="badge bg-success">
                                    <i class="bx bx-check me-1"></i>
                                    Returned {{ $allocation->return_date->format('d M Y') }}
                                </span>
                            @else
                                <span class="badge bg-warning text-dark">
                                    <i class="bx bx-time me-1"></i>
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td>{{ $allocation->condition_on_allocation ?? 'N/A' }}</td>
                        <td>{{ $allocation->condition_on_return ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Summary --}}
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="info-item">
                <div class="label">Total Books Allocated</div>
                <div class="value">{{ $student->bookAllocations->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-item" style="border-left-color: #10b981;">
                <div class="label">Books Returned</div>
                <div class="value">{{ $student->bookAllocations->whereNotNull('return_date')->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-item" style="border-left-color: #f59e0b;">
                <div class="label">Books Pending</div>
                <div class="value">{{ $student->bookAllocations->whereNull('return_date')->count() }}</div>
            </div>
        </div>
    </div>
@else
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bx bx-book"></i>
        </div>
        <h5>No Books Allocated</h5>
        <p>No books have been allocated to this student yet.</p>
    </div>
@endif

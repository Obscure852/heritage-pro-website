{{-- Behavior Records Partial --}}
<div class="help-text mb-4">
    <div class="help-title">Behavior Records</div>
    <div class="help-content">
        View your child's behavior records including both positive recognition and any incidents that have been reported.
    </div>
</div>

@if($student->studentbehaviour && $student->studentbehaviour->count() > 0)
    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        @php
            $positiveCount = $student->studentbehaviour->where('behaviour_type', 'Positive')->count();
            $negativeCount = $student->studentbehaviour->where('behaviour_type', 'Negative')->count();
        @endphp
        <div class="col-md-6">
            <div class="info-item" style="border-left-color: #10b981;">
                <div class="label">Positive Reports</div>
                <div class="value">
                    <span class="badge bg-success fs-6">{{ $positiveCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-item" style="border-left-color: #ef4444;">
                <div class="label">Negative Reports</div>
                <div class="value">
                    <span class="badge bg-danger fs-6">{{ $negativeCount }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Behavior Table --}}
    <div class="subject-table-container">
        <table class="table table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 120px;">Date</th>
                    <th style="width: 100px;">Type</th>
                    <th>Description</th>
                    <th>Action Taken</th>
                    <th style="width: 150px;">Reported By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($student->studentbehaviour->sortByDesc('date') as $behaviour)
                    <tr>
                        <td>{{ $behaviour->date?->format('d M Y') ?? 'N/A' }}</td>
                        <td>
                            @if($behaviour->behaviour_type === 'Positive')
                                <span class="badge bg-success">
                                    <i class="bx bx-check-circle me-1"></i>
                                    Positive
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="bx bx-x-circle me-1"></i>
                                    Negative
                                </span>
                            @endif
                        </td>
                        <td>{{ $behaviour->description ?? 'N/A' }}</td>
                        <td>{{ $behaviour->action_taken ?? '-' }}</td>
                        <td>{{ $behaviour->reported_by ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="empty-state">
        <div class="empty-state-icon" style="background: #d1fae5;">
            <i class="bx bx-check-circle" style="color: #10b981;"></i>
        </div>
        <h5>No Behavior Records</h5>
        <p>No behavior incidents have been recorded for this student. Keep up the good work!</p>
    </div>
@endif

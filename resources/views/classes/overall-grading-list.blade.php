<style>
    /* Grading Card */
    .grading-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
    }

    .grading-card-body {
        padding: 20px;
    }

    /* Actions Row */
    .actions-row {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-bottom: 20px;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        border-radius: 3px;
        color: white;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-action:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }

    /* Table Styling */
    .grading-table {
        width: 100%;
        border-collapse: collapse;
    }

    .grading-table thead th {
        background: #f9fafb;
        padding: 12px 16px;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
    }

    .grading-table tbody td {
        padding: 12px 16px;
        color: #4b5563;
        font-size: 14px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .grading-table tbody tr:hover {
        background: #f9fafb;
    }

    .grading-table tbody tr:last-child td {
        border-bottom: none;
    }
</style>

<div class="grading-card">
    <div class="grading-card-body">
        @if (session('message'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="actions-row">
            @can('manage-academic')
                @if (!session('is_past_term'))
                    <a href="{{ route('academic.edit-overall-grading', ['gradeId' => $gradeId]) }}" class="btn-action">
                        <i class="bx bx-edit"></i> Edit Grading
                    </a>
                    <a href="{{ route('academic.add-overall-grading') }}" class="btn-action">
                        <i class="bx bx-plus"></i> Add Grading
                    </a>
                @endif
            @endcan
        </div>

        <div class="table-responsive">
            <table class="grading-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Min Score</th>
                        <th>Max Score</th>
                        <th>Grade</th>
                        <th>Year</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($overall as $index => $grade)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $grade->min_score }}</td>
                            <td>{{ $grade->max_score }}</td>
                            <td><strong>{{ $grade->grade }}</strong></td>
                            <td>{{ $grade->year }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

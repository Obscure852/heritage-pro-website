<style>
    /* Matrix Card */
    .matrix-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
    }

    .matrix-card-body {
        padding: 20px;
    }

    /* Header Row */
    .matrix-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    .matrix-title {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .matrix-title i {
        color: #3b82f6;
    }

    .btn-edit {
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

    .btn-edit:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
    }

    /* Table Styling */
    .matrix-table {
        width: 100%;
        border-collapse: collapse;
    }

    .matrix-table thead th {
        background: #f9fafb;
        padding: 12px 16px;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
    }

    .matrix-table tbody td {
        padding: 12px 16px;
        color: #4b5563;
        font-size: 14px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .matrix-table tbody tr:hover {
        background: #f9fafb;
    }

    .matrix-table tbody tr:last-child td {
        border-bottom: none;
    }
</style>

<div class="matrix-card">
    <div class="matrix-card-body">
        @if (session('message'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="matrix-header">
            <h5 class="matrix-title">
                <i class="bx bx-bar-chart-alt-2"></i>
                Points Matrix for {{ $grade->name ?? 'Current Grade' }}
            </h5>
            @can('manage-academic')
                @if (!session('is_past_term'))
                    <a href="{{ route('academic.edit-overall-points', ['academicYear' => $grade->name]) }}" class="btn-edit">
                        <i class="bx bx-edit"></i> Edit Points Matrix
                    </a>
                @endif
            @endcan
        </div>

        <div class="table-responsive">
            <table class="matrix-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Min Points</th>
                        <th>Max Points</th>
                        <th>Grade</th>
                        <th>Academic Year</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($overall as $index => $grade)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $grade->min }}</td>
                            <td>{{ $grade->max }}</td>
                            <td><strong>{{ $grade->grade }}</strong></td>
                            <td>{{ $grade->academic_year }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

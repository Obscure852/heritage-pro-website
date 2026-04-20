<style>
    /* Table Styling */
    .classes-table {
        width: 100%;
        border-collapse: collapse;
    }

    .classes-table thead th {
        background: #f9fafb;
        padding: 12px 16px;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
    }

    .classes-table tbody td {
        padding: 12px 16px;
        color: #4b5563;
        font-size: 14px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .classes-table tbody tr:hover {
        background: #f9fafb;
    }

    .classes-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Action Button Styles */
    .table-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 3px;
        border: none;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .table-action-btn.view {
        background: #e0f2fe;
        color: #0284c7;
    }

    .table-action-btn.view:hover {
        background: #0284c7;
        color: white;
    }

    .table-action-btn.allocate {
        background: #dbeafe;
        color: #2563eb;
    }

    .table-action-btn.allocate:hover {
        background: #2563eb;
        color: white;
    }

    .table-action-btn.edit {
        background: #fef3c7;
        color: #d97706;
    }

    .table-action-btn.edit:hover {
        background: #d97706;
        color: white;
    }

    .table-action-btn.delete {
        background: #fee2e2;
        color: #dc2626;
    }

    .table-action-btn.delete:hover {
        background: #dc2626;
        color: white;
    }

    /* Text Muted Style */
    .text-muted-custom {
        color: #9ca3af;
        font-style: italic;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 48px;
        color: #d1d5db;
        margin-bottom: 16px;
    }

    .empty-state h5 {
        color: #374151;
        margin-bottom: 8px;
    }

    .empty-state p {
        margin: 0;
        font-size: 14px;
    }
</style>

<div class="table-responsive">
    @if (!empty($classes) && $classes->count() > 0)
        <table class="classes-table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Class Teacher</th>
                    <th scope="col">No. of Students</th>
                    <th scope="col">Monitor</th>
                    <th scope="col">Monitress</th>
                    <th scope="col">Grade</th>
                    <th scope="col">Year</th>
                    <th scope="col" style="width: 140px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($classes as $index => $klass)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $klass->name ?? '' }}</strong></td>
                        <td>
                            @if ($klass->teacher)
                                {{ $klass->teacher->full_name }}
                            @else
                                <span class="text-muted-custom">Former Teacher (Deleted)</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $klass->students->count() ?? 0 }}</span>
                        </td>
                        @php($monitor = $klass->monitor)
                        @php($monitress = $klass->monitress)
                        <td>
                            @if ($monitor)
                                {{ $monitor->full_name }}
                            @else
                                <span class="text-muted-custom">Not Allocated</span>
                            @endif
                        </td>
                        <td>
                            @if ($monitress)
                                {{ $monitress->full_name }}
                            @else
                                <span class="text-muted-custom">Not Allocated</span>
                            @endif
                        </td>
                        <td>{{ $klass->grade->name ?? '' }}</td>
                        <td>{{ $klass->year ?? '' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('academic.show', $klass->id) }}"
                                    class="table-action-btn view" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="View Class List">
                                    <i class="bx bx-show"></i>
                                </a>

                                @can('class-allocation-teacher', $klass)
                                    <a href="{{ route('academic.allocate-students', ['id' => $klass->id, 'termId' => $klass->term_id]) }}"
                                        class="table-action-btn allocate" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Allocate Students">
                                        <i class="bx bx-layer"></i>
                                    </a>
                                @endcan

                                @can('class-allocation-teacher', $klass)
                                    <a href="{{ route('academic.edit', $klass) }}"
                                        class="table-action-btn edit" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Edit Class">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                @endcan

                                @can('manage-academic')
                                    <a href="{{ route('academic.delete-class', $klass->id) }}"
                                        class="table-action-btn delete" data-bs-toggle="tooltip"
                                        data-bs-placement="top" onclick="return confirmDeleteClass()"
                                        title="Delete Class">
                                        <i class="bx bx-trash"></i>
                                    </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-state">
            <i class="bx bx-folder-open"></i>
            <h5>No Classes Found</h5>
            <p>There are no classes for the selected term and grade. Create a new class to get started.</p>
        </div>
    @endif
</div>

<script>
    $(document).ready(function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th style="width: 60px;">#</th>
                <th>Holiday Name</th>
                <th style="width: 120px;">Start Date</th>
                <th style="width: 120px;">End Date</th>
                <th style="width: 80px;">Days</th>
                <th style="width: 100px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($holidays as $index => $holiday)
                <tr>
                    <td>
                        <span class="text-muted">{{ $index + 1 }}</span>
                    </td>
                    <td>
                        <span class="fw-medium">{{ $holiday->name }}</span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($holiday->start_date)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($holiday->end_date)->format('d M Y') }}</td>
                    <td>
                        @php
                            $days = \Carbon\Carbon::parse($holiday->start_date)->diffInDays(\Carbon\Carbon::parse($holiday->end_date)) + 1;
                        @endphp
                        <span class="badge bg-secondary">{{ $days }} {{ Str::plural('day', $days) }}</span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary action-btn"
                                data-id="{{ $holiday->id }}"
                                data-name="{{ $holiday->name }}"
                                data-term-id="{{ $holiday->term_id }}"
                                data-start="{{ \Carbon\Carbon::parse($holiday->start_date)->format('Y-m-d') }}"
                                data-end="{{ \Carbon\Carbon::parse($holiday->end_date)->format('Y-m-d') }}"
                                onclick="openEditHolidayModal(this)"
                                title="Edit Holiday">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('holidays.delete-holiday', $holiday->id) }}"
                                method="POST"
                                style="display: inline;"
                                onsubmit="return confirm('Are you sure you want to delete this holiday?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger action-btn" title="Delete Holiday">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fas fa-calendar-times fa-2x mb-2 d-block opacity-50"></i>
                        No holidays found for the selected term. Click "Add Holiday" to create one.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

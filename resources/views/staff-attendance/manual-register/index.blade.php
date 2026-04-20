@extends('layouts.master')
@section('title')
    Manual Attendance Register
@endsection
@section('css')
    <style>
        .admissions-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .admissions-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .admissions-body {
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

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Grid-specific styles */
        .attendance-grid {
            overflow-x: auto;
        }

        .attendance-grid table {
            min-width: 600px;
        }

        .attendance-grid th {
            text-align: center;
            padding: 12px 8px;
            background: #f9fafb;
            font-weight: 700;
            font-size: 13px;
            color: #1f2937;
            border-bottom: 2px solid #e5e7eb;
        }

        .attendance-grid th.day-header {
            cursor: pointer;
            transition: background 0.2s;
            min-width: 62px;
            width: 62px;
            padding: 10px 6px;
            font-size: 13px;
            font-weight: 700;
        }

        .attendance-grid th.day-header:hover {
            background: #f3f4f6;
        }

        .attendance-grid th.today-header {
            background: #e0e7ff;
            border-bottom-color: #6366f1;
        }

        .attendance-grid td {
            text-align: center;
            padding: 8px;
            vertical-align: middle;
        }

        .attendance-cell {
            cursor: pointer;
            padding: 6px 5px !important;
            transition: background 0.1s;
            width: 62px;
        }

        .attendance-cell:hover {
            background: #f3f4f6;
        }

        .attendance-cell.today-cell {
            background: #f5f3ff;
        }

        .attendance-cell.today-cell:hover {
            background: #ede9fe;
        }

        .attendance-cell.leave-protected {
            cursor: not-allowed;
            background: #fef3c7;
            position: relative;
        }

        .attendance-cell.leave-protected:hover {
            background: #fef3c7;
        }

        .attendance-cell.leave-protected .attendance-code {
            transform: none !important;
        }

        .attendance-cell.leave-protected::before {
            content: '';
            position: absolute;
            top: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2392400e'%3E%3Cpath fill-rule='evenodd' d='M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z' clip-rule='evenodd'/%3E%3C/svg%3E") no-repeat center;
            background-size: contain;
            z-index: 1;
        }

        .attendance-code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 28px;
            padding: 0 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 12px;
            color: white;
            transition: transform 0.1s;
        }

        .attendance-cell:hover .attendance-code {
            transform: scale(1.1);
        }

        .attendance-code.has-notes {
            position: relative;
        }

        .attendance-code.has-notes::after {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background: #f59e0b;
            border-radius: 50%;
            border: 1px solid white;
        }

        .staff-name {
            text-align: center;
            font-weight: 600;
            white-space: nowrap;
            color: #1f2937;
        }

        .gender-male {
            color: #007bff;
            font-weight: 600;
        }

        .gender-female {
            color: #e83e8c;
            font-weight: 600;
        }

        .staff-department {
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }

        .week-nav-inline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .week-nav-inline .btn {
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 600;
            background-color: #1f2937;
            border-color: #1f2937;
            color: white;
        }

        .week-nav-inline .btn:hover {
            background-color: #111827;
            border-color: #111827;
            color: white;
        }

        .week-label-inline {
            font-weight: 700;
            color: #1f2937;
            font-size: 14px;
            white-space: nowrap;
        }

        .week-nav-row th {
            border-bottom: none !important;
        }

        .filter-section {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .filter-section label {
            font-weight: 500;
            margin-right: 8px;
        }

        .filter-section select {
            min-width: 200px;
        }

        /* Reports Dropdown Styling */
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
            min-width: 240px;
            margin-top: 4px;
        }

        .reports-dropdown .dropdown-item {
            padding: 10px 16px;
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
            color: #6b7280;
        }

        .reports-dropdown .dropdown-item:hover i {
            color: #3b82f6;
        }

        .attendance-legend {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .legend-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 22px;
            padding: 0 6px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 11px;
            color: white;
        }

        .legend-label {
            font-size: 12px;
            color: #4b5563;
            font-weight: 500;
        }

        .unsaved-indicator {
            display: none;
            color: #f59e0b;
            font-size: 12px;
            font-weight: 500;
        }

        .unsaved-indicator.show {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Code option styling for modal */
        .code-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s;
            margin-bottom: 4px;
        }

        .code-option:hover {
            background: #f3f4f6;
        }

        .code-option.selected {
            background: #e0e7ff;
            border: 2px solid #6366f1;
        }

        .code-option .code-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 24px;
            padding: 0 6px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 11px;
            color: white;
        }

        .code-option .code-label {
            font-size: 14px;
            color: #374151;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .admissions-header {
                padding: 20px;
            }

            .admissions-body {
                padding: 16px;
            }

            .filter-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .week-label-inline {
                font-size: 10px;
            }
        }
    </style>
@endsection
@section('content')
    @if (session('success'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') }}</strong>
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

    <div class="admissions-container">
        <div class="admissions-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Manual Attendance Register</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Record weekly staff attendance</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['present'] }}</h4>
                                <small class="opacity-75">Present Today</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['absent'] }}</h4>
                                <small class="opacity-75">Absent Today</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['late'] }}</h4>
                                <small class="opacity-75">Late Today</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $stats['on_leave'] }}</h4>
                                <small class="opacity-75">On Leave Today</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="admissions-body">
            <div class="help-text">
                <div class="help-title">How to Use</div>
                <div class="help-content">
                    Click any cell to cycle through attendance codes: <strong>✓ Present → A Absent → L Late → ...</strong>
                    and back to blank.
                    Click a column header (day) to apply the same code to all staff for that day.
                    Changes are tracked automatically - click 'Save Changes' when done.
                </div>
            </div>

            <div class="filter-section" style="justify-content: space-between;">
                <div>
                    <label for="department">Department:</label>
                    <select id="department" class="form-select" onchange="filterByDepartment(this.value)">
                        <option value="">All Departments</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept }}" {{ $departmentFilter == $dept ? 'selected' : '' }}>
                                {{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="btn-group reports-dropdown">
                    <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2"
                            style="font-size: 10px;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('staff-attendance.reports.daily') }}">
                                <i class="fas fa-calendar-day"></i> Daily Attendance Report
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('staff-attendance.reports.monthly') }}">
                                <i class="fas fa-calendar-alt"></i> Monthly Summary Report
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('staff-attendance.reports.department') }}">
                                <i class="fas fa-building"></i> Department Comparison
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('staff-attendance.reports.punctuality') }}">
                                <i class="fas fa-clock"></i> Punctuality Report
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('staff-attendance.reports.absenteeism') }}">
                                <i class="fas fa-user-times"></i> Absenteeism Report
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('staff-attendance.reports.hours-worked') }}">
                                <i class="fas fa-hourglass-half"></i> Hours Worked Report
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="attendance-legend">
                    @foreach ($codes as $code)
                        <span class="legend-item">
                            <span class="legend-badge" style="background: {{ $code->color }};">
                                @if ($code->code === 'P')
                                    <i class="fas fa-check"></i>
                                @else
                                    {{ $code->code }}
                                @endif
                            </span>
                            <span class="legend-label">{{ $code->description }}</span>
                        </span>
                    @endforeach
                    <span class="legend-item" title="Linked to approved leave request - cannot be edited">
                        <span class="legend-badge" style="background: #fef3c7; color: #92400e;">
                            <i class="fas fa-lock" style="font-size: 10px;"></i>
                        </span>
                        <span class="legend-label">Approved Leave</span>
                    </span>
                    <span class="unsaved-indicator">
                        <i class="fas fa-exclamation-triangle"></i> Unsaved
                    </span>
                </div>
                <button type="button" class="btn btn-primary btn-loading save-btn" onclick="saveAttendance()">
                    <span class="btn-text"><i class="fas fa-save"></i> Save</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>

            <div class="attendance-grid">
                <table class="table table-bordered">
                    <thead>
                        <tr class="week-nav-row">
                            <th colspan="4" style="border: none; background: transparent;"></th>
                            <th colspan="{{ count($weekdays) }}"
                                style="text-align: center; background: #f8fafc; padding: 10px 8px;">
                                <div class="week-nav-inline">
                                    <button type="button" class="btn btn-dark" onclick="navigateWeek(-1)">
                                        <i class="fas fa-chevron-left me-1"></i> Previous Week
                                    </button>
                                    <span class="week-label-inline">{{ \Carbon\Carbon::parse($weekStart)->format('M d') }}
                                        - {{ \Carbon\Carbon::parse($weekEnd)->format('M d, Y') }}</span>
                                    <button type="button" class="btn btn-dark" onclick="navigateWeek(1)">
                                        Next Week <i class="fas fa-chevron-right ms-1"></i>
                                    </button>
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 180px; text-align: center;">Staff Member</th>
                            <th style="width: 60px; text-align: center;">Gender</th>
                            <th style="width: 110px; text-align: center;">ID Number</th>
                            <th style="width: 55px; text-align: center;">Absent</th>
                            @foreach ($weekdays as $day)
                                <th class="day-header {{ $day['isToday'] ? 'today-header' : '' }}"
                                    data-date="{{ $day['date'] }}" onclick="toggleColumn(this)"
                                    title="Click to apply code to all">
                                    {{ substr($day['day'], 0, 3) }}<br>
                                    <small
                                        style="font-size: 11px; font-weight: 600;">{{ \Carbon\Carbon::parse($day['date'])->format('d') }}</small>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $member)
                            <tr>
                                <td class="staff-name">
                                    {{ $member->lastname }}, {{ $member->firstname }}
                                    @if ($member->department)
                                        <div class="staff-department">{{ $member->department }}</div>
                                    @endif
                                </td>
                                <td style="text-align: center; font-size: 13px;">
                                    @if ($member->gender == 'M' || $member->gender == 'Male')
                                        <span class="gender-male"><i class="fas fa-mars"></i> M</span>
                                    @elseif ($member->gender == 'F' || $member->gender == 'Female')
                                        <span class="gender-female"><i class="fas fa-venus"></i> F</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="text-align: center; font-size: 13px; color: #374151; font-weight: 500;">
                                    {{ $member->formatted_id_number ?? ($member->id_number ?? '-') }}
                                </td>
                                <td style="text-align: center; font-size: 13px;">
                                    @php
                                        $absentDays = $staffAbsences[$member->id] ?? 0;
                                    @endphp
                                    <span class="{{ $absentDays > 0 ? 'text-danger fw-bold' : 'text-muted' }}"
                                        style="font-weight: 600;">
                                        {{ $absentDays }}/{{ $totalWorkDays }}
                                    </span>
                                </td>
                                @foreach ($weekdays as $day)
                                    @php
                                        $key = $member->id . '_' . $day['date'];
                                        $record = $attendances[$key] ?? null;
                                        $code = $record?->attendanceCode;
                                        $codeIndex = $code ? $codes->search(fn($c) => $c->id === $code->id) : -1;
                                        $existingNotes = $record?->notes ?? '';
                                        $isLeaveProtected = in_array($key, $leaveProtectedCells ?? []);
                                    @endphp
                                    <td class="attendance-cell {{ $day['isToday'] ? 'today-cell' : '' }} {{ $isLeaveProtected ? 'leave-protected' : '' }}"
                                        data-user-id="{{ $member->id }}"
                                        data-user-name="{{ $member->lastname }}, {{ $member->firstname }}"
                                        data-date="{{ $day['date'] }}"
                                        data-date-display="{{ \Carbon\Carbon::parse($day['date'])->format('D, M d') }}"
                                        data-code-index="{{ $codeIndex }}" data-notes="{{ $existingNotes }}"
                                        data-leave-protected="{{ $isLeaveProtected ? 'true' : 'false' }}"
                                        onclick="toggleCode(this)"
                                        @if ($isLeaveProtected) title="On approved leave - cannot be edited" @endif>
                                        @if ($code)
                                            <span class="attendance-code {{ $existingNotes ? 'has-notes' : '' }}"
                                                style="background: {{ $code->color }};">
                                                @if ($code->code === 'P')
                                                    <i class="fas fa-check"></i>
                                                @else
                                                    {{ $code->code }}
                                                @endif
                                            </span>
                                        @else
                                            <span class="attendance-code"
                                                style="background: #d1d5db; color: #6b7280;">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($weekdays) + 4 }}" class="text-center text-muted py-5">
                                    <i class="fas fa-users" style="font-size: 48px; opacity: 0.3;"></i>
                                    <p class="mt-3 mb-0">No staff found for the selected criteria</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end align-items-center mt-3 gap-3">
                <span class="unsaved-indicator">
                    <i class="fas fa-exclamation-triangle"></i> Unsaved
                </span>
                <button type="button" class="btn btn-primary btn-loading save-btn" onclick="saveAttendance()">
                    <span class="btn-text"><i class="fas fa-save"></i> Save</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </div>
    </div>

@endsection
@section('script')
    @php
        $codesArray = $codes
            ->map(function ($c) {
                return ['id' => $c->id, 'code' => $c->code, 'color' => $c->color, 'description' => $c->description];
            })
            ->values()
            ->toArray();
    @endphp
    <script>
        // Attendance codes from controller
        // Order: P (Present), A (Absent), L (Late), etc., then blank (-1)
        const attendanceCodes = @json($codesArray);
        const BLANK_CODE_INDEX = -1;

        // Track changes
        let pendingChanges = {};

        document.addEventListener('DOMContentLoaded', function() {
            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    }
                }, 5000);
            });
        });

        // Get next code index in cycle: -1 -> 0 -> 1 -> ... -> N-1 -> -1
        function getNextCodeIndex(currentIndex) {
            if (currentIndex === BLANK_CODE_INDEX) {
                return 0; // First code (P - Present)
            }
            const nextIndex = currentIndex + 1;
            if (nextIndex >= attendanceCodes.length) {
                return BLANK_CODE_INDEX; // Back to blank
            }
            return nextIndex;
        }

        // Toggle a single cell's attendance code
        function toggleCode(cell) {
            // Skip leave-protected cells
            if (cell.dataset.leaveProtected === 'true') {
                Swal.fire({
                    icon: 'info',
                    title: 'Leave Day',
                    text: 'This day is linked to an approved leave request and cannot be edited here. To modify, please update the leave request.',
                    timer: 3000,
                    showConfirmButton: false
                });
                return;
            }

            const userId = cell.dataset.userId;
            const date = cell.dataset.date;
            let currentIndex = parseInt(cell.dataset.codeIndex);

            // Get next code in cycle
            const nextIndex = getNextCodeIndex(currentIndex);

            // Update cell display
            updateCellDisplay(cell, nextIndex);

            // Update data attribute
            cell.dataset.codeIndex = nextIndex;

            // Track change
            trackChange(userId, date, nextIndex);
        }

        // Toggle all cells in a column (day)
        function toggleColumn(header) {
            const date = header.dataset.date;
            const cells = document.querySelectorAll('.attendance-cell[data-date="' + date + '"]:not(.leave-protected)');

            if (cells.length === 0) return;

            // Get current index from first non-protected cell to determine next
            let currentIndex = parseInt(cells[0].dataset.codeIndex);
            const nextIndex = getNextCodeIndex(currentIndex);

            // Apply to all non-protected cells in column
            cells.forEach(function(cell) {
                const userId = cell.dataset.userId;

                // Update cell display
                updateCellDisplay(cell, nextIndex);

                // Update data attribute
                cell.dataset.codeIndex = nextIndex;

                // Track change
                trackChange(userId, date, nextIndex);
            });
        }

        // Update cell display based on code index
        function updateCellDisplay(cell, codeIndex) {
            let codeSpan = document.createElement('span');
            codeSpan.className = 'attendance-code';

            if (codeIndex >= 0 && attendanceCodes[codeIndex]) {
                const code = attendanceCodes[codeIndex];
                codeSpan.style.background = code.color;
                codeSpan.style.color = '#fff';

                // Show checkmark for Present (P), otherwise show code
                if (code.code === 'P') {
                    codeSpan.innerHTML = '<i class="fas fa-check"></i>';
                } else {
                    codeSpan.textContent = code.code;
                }
            } else {
                // Blank / no code
                codeSpan.style.background = '#d1d5db';
                codeSpan.style.color = '#6b7280';
                codeSpan.textContent = '-';
            }

            cell.innerHTML = '';
            cell.appendChild(codeSpan);
        }

        // Track a change for later saving
        function trackChange(userId, date, codeIndex) {
            const key = userId + '_' + date;
            pendingChanges[key] = {
                user_id: parseInt(userId),
                date: date,
                attendance_code_id: codeIndex >= 0 ? attendanceCodes[codeIndex].id : null,
                notes: null
            };

            // Show both unsaved indicators
            document.querySelectorAll('.unsaved-indicator').forEach(el => el.classList.add('show'));
        }

        // Save all pending changes
        function saveAttendance() {
            const changes = Object.values(pendingChanges);
            if (changes.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Changes',
                    text: 'There are no pending changes to save.',
                    timer: 2000,
                    showConfirmButton: false
                });
                return;
            }

            // Disable all save buttons
            const saveBtns = document.querySelectorAll('.save-btn');
            saveBtns.forEach(btn => {
                btn.classList.add('loading');
                btn.disabled = true;
            });

            fetch('{{ route('staff-attendance.manual-register.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        attendances: changes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Re-enable all save buttons
                    saveBtns.forEach(btn => {
                        btn.classList.remove('loading');
                        btn.disabled = false;
                    });

                    if (data.success) {
                        pendingChanges = {};
                        // Hide both unsaved indicators
                        document.querySelectorAll('.unsaved-indicator').forEach(el => el.classList.remove('show'));

                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: data.message + ' (' + data.count + ' records)',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to save attendance'
                        });
                    }
                })
                .catch(error => {
                    // Re-enable all save buttons
                    saveBtns.forEach(btn => {
                        btn.classList.remove('loading');
                        btn.disabled = false;
                    });

                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while saving. Please try again.'
                    });
                });
        }

        // Navigate to a different week
        function navigateWeek(direction) {
            // Warn about unsaved changes
            if (Object.keys(pendingChanges).length > 0) {
                if (!confirm('You have unsaved changes. Are you sure you want to leave this page?')) {
                    return;
                }
            }

            const currentStart = new Date('{{ $weekStart->format('Y-m-d') }}');
            currentStart.setDate(currentStart.getDate() + (direction * 7));
            const newDate = currentStart.toISOString().split('T')[0];

            const url = new URL(window.location.href);
            url.searchParams.set('week_start', newDate);
            window.location.href = url.toString();
        }

        // Filter by department
        function filterByDepartment(dept) {
            // Warn about unsaved changes
            if (Object.keys(pendingChanges).length > 0) {
                if (!confirm('You have unsaved changes. Are you sure you want to leave this page?')) {
                    document.getElementById('department').value = '{{ $departmentFilter ?? '' }}';
                    return;
                }
            }

            const url = new URL(window.location.href);
            if (dept) {
                url.searchParams.set('department', dept);
            } else {
                url.searchParams.delete('department');
            }
            window.location.href = url.toString();
        }

        // Warn before leaving with unsaved changes
        window.addEventListener('beforeunload', function(e) {
            if (Object.keys(pendingChanges).length > 0) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
@endsection

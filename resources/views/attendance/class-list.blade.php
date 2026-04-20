@if (!empty($klass))
    <style>
        .class-info-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            border-radius: 3px;
            color: white;
            padding: 12px 16px;
            margin-bottom: 16px;
        }

        .class-info-header p {
            margin: 0;
            font-size: 14px;
        }

        .class-info-header strong {
            font-weight: 600;
        }

        .attendance-table-container {
            background: white;
            border-radius: 3px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Controls row */
        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        /* Attendance Codes Legend */
        .attendance-legend {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #374151;
        }

        .legend-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 24px;
            padding: 0 6px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            color: white;
        }

        /* Week Navigation */
        .week-navigation {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .week-nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }

        .week-nav-btn.prev {
            background: #1f2937;
            color: white;
        }

        .week-nav-btn.prev:hover {
            background: #374151;
        }

        .week-nav-btn.next {
            background: #1f2937;
            color: white;
        }

        .week-nav-btn.next:hover:not(.disabled) {
            background: #374151;
        }

        .week-nav-btn.disabled {
            background: #e5e7eb !important;
            color: #9ca3af !important;
            opacity: 0.7;
            cursor: not-allowed;
            pointer-events: none;
        }

        .week-date-display {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            font-weight: 500;
            color: #374151;
        }

        .week-date-display i {
            color: #6b7280;
        }

        /* Sortable Headers */
        .sortable {
            cursor: pointer;
            user-select: none;
            position: relative;
            padding-right: 20px !important;
        }

        .sortable:hover {
            background: #f3f4f6 !important;
        }

        .sortable::after {
            content: '\f0dc';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 6px;
            color: #9ca3af;
            font-size: 10px;
        }

        .sortable.asc::after {
            content: '\f0de';
            color: #3b82f6;
        }

        .sortable.desc::after {
            content: '\f0dd';
            color: #3b82f6;
        }

        /* Table Styles - matching admissions */
        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 10px 8px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 8px;
            vertical-align: middle;
            color: #4b5563;
            font-size: 13px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .day-header {
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            min-width: 50px;
        }

        .day-header:hover {
            background: #eff6ff !important;
        }

        .day-header .date-label {
            display: block;
            font-size: 10px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 2px;
        }

        .day-header .day-letter {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }

        /* Week Divider */
        .week-divider {
            width: 12px !important;
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
        }

        .week-divider-cell {
            width: 12px !important;
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
        }

        /* Week Group Borders */
        /* All week cells get borders */
        .week-1,
        .week-2 {
            border: 1px solid #e5e7eb !important;
        }

        /* Border radius on corners */
        thead th.week-start {
            border-top-left-radius: 3px !important;
        }

        thead th.week-end {
            border-top-right-radius: 3px !important;
        }

        tbody tr:last-child td.week-start {
            border-bottom-left-radius: 3px !important;
        }

        tbody tr:last-child td.week-end {
            border-bottom-right-radius: 3px !important;
        }

        /* Attendance Input */
        .attendance-input {
            width: 35px !important;
            height: 35px !important;
            text-align: center;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 12px;
            font-weight: 500;
        }

        .attendance-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .attendance-input:hover {
            border-color: #3b82f6;
        }

        /* Gender styles */
        .gender-male {
            color: #007bff;
        }

        .gender-female {
            color: #e83e8c;
        }

        /* Save Button */
        .btn-save-attendance {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-save-attendance:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-save-attendance.loading .btn-text {
            display: none;
        }

        .btn-save-attendance.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-save-attendance:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-spinner {
            display: none;
        }

        /* Manual Entry Link */
        .manual-entry-link {
            color: #6b7280;
            transition: color 0.2s ease;
        }

        .manual-entry-link:hover {
            color: #3b82f6;
        }

        .manual-entry-link.has-entry {
            color: #3b82f6;
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .pagination-info {
            color: #6b7280;
            font-size: 13px;
        }

        .pagination {
            margin-bottom: 0;
        }

        .pagination .page-link {
            border: 1px solid #d1d5db;
            color: #374151;
            padding: 6px 12px;
            font-size: 14px;
        }

        .pagination .page-item.active .page-link {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .pagination .page-item.disabled .page-link {
            color: #9ca3af;
        }

        .student-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            color: #fff;
            margin-left: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Today's Column Highlight */
        .today-column {
            background-color: #eff6ff !important;
            border-left: 2px solid #3b82f6 !important;
            border-right: 2px solid #3b82f6 !important;
        }

        thead th.today-column {
            background-color: #dbeafe !important;
            border-top: 2px solid #3b82f6 !important;
        }

        tbody tr:last-child td.today-column {
            border-bottom: 2px solid #3b82f6 !important;
        }

        .today-column .date-label {
            color: #2563eb !important;
            font-weight: 600 !important;
        }

        .today-column .day-letter {
            color: #1d4ed8 !important;
        }
    </style>

    <div class="class-info-header">
        <p>
            <i class="fas fa-chalkboard-teacher me-2"></i>
            <strong>{{ $klass->name ?? '' }}</strong>
            <span class="mx-2">|</span>
            Teacher: {{ $klass->teacher->fullName ?? 'Not Assigned' }}
            <span class="mx-2">|</span>
            <i class="fas fa-users me-1"></i> {{ $klass->students->count() ?? 0 }} Students
        </p>
    </div>

    <div class="attendance-table-container">
        @php
            $weekOneCount = count($weekOneDays ?? []);
            $weekTwoCount = count($weekTwoDays ?? []);
            $hasWeekDivider = $weekOneCount > 0 && $weekTwoCount > 0;
            $tableColumnCount = 6 + count($daysOfWeek) + ($hasWeekDivider ? 1 : 0);
        @endphp

        <form method="POST" action="{{ route('attendance.store') }}" id="attendanceForm">
            @csrf

            <div class="d-flex justify-content-end mb-3">
                @can('class-teacher', $klass)
                    @if (!session('is_past_term'))
                        <button type="submit" class="btn-save-attendance" id="saveAttendanceBtnTop">
                            <span class="btn-text"><i class="fas fa-save me-1"></i> Save Attendance</span>
                            <span class="btn-spinner">
                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                    aria-hidden="true"></span>
                                Saving...
                            </span>
                        </button>
                    @endif
                @endcan
            </div>

            <input name="term" type="hidden" value="{{ $klass->term_id }}">
            <input name="year" type="hidden" value="{{ $klass->year }}">
            <input name="klass" type="hidden" value="{{ $klass->id }}">
            <input name="currentWeekStart" type="hidden" id="currentWeekStart" value="{{ $currentWeekStart }}">

            <!-- Attendance Codes Legend -->
            <div class="attendance-legend">
                @foreach ($attendanceCodes as $code)
                    <div class="legend-item">
                        <span class="legend-badge"
                            style="background-color: {{ $code->color }};">{{ $code->code }}</span>
                        <span>{{ $code->description }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Week Navigation -->
            <div class="week-navigation">
                <button type="button" class="week-nav-btn prev {{ $canGoPrevious ? '' : 'disabled' }}" id="prevWeek"
                    {{ $canGoPrevious ? '' : 'disabled' }}>
                    <i class="fas fa-chevron-left"></i> Previous Week
                </button>
                <div class="week-date-display">
                    <i class="fas fa-calendar-alt"></i>
                    <span>
                        @if ($visibleStartDate && $visibleEndDate)
                            {{ \Carbon\Carbon::parse($visibleStartDate)->format('M d') }} -
                            {{ \Carbon\Carbon::parse($visibleEndDate)->format('M d, Y') }}
                        @else
                            No school days in this range
                        @endif
                    </span>
                </div>
                <button type="button" class="week-nav-btn next {{ $canGoNext ? '' : 'disabled' }}" id="nextWeek"
                    {{ $canGoNext ? '' : 'disabled' }}>
                    Next Week <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <div class="table-responsive">
                <table id="studentTable" class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th style="width: 40px;">Entry</th>
                            <th class="sortable" data-sort="firstname">First Name</th>
                            <th class="sortable" data-sort="lastname">Last Name</th>
                            <th class="sortable" data-sort="genderSort">Gender</th>
                            <th>Absent</th>
                            @foreach ($daysOfWeek as $index => $dayOfWeek)
                                @if ($hasWeekDivider && $index === $weekOneCount)
                                    <th class="week-divider"></th>
                                @endif
                                <th class="attendance-header day-header {{ $dayOfWeek['weekClass'] }} {{ $dayOfWeek['positionClass'] }} {{ $dayOfWeek['date'] === \Carbon\Carbon::today()->toDateString() ? 'today-column' : '' }}"
                                    data-day="{{ $dayOfWeek['date'] }}" data-week="{{ $currentWeekStart }}">
                                    <span
                                        class="date-label">{{ \Carbon\Carbon::parse($dayOfWeek['date'])->format('j M') }}</span>
                                    <span class="day-letter">{{ $dayOfWeek['day'] }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $studentIds = $klass->students->pluck('id')->toArray();
                            $totalStudents = count($klass->students);
                        @endphp
                        @forelse ($klass->students as $index => $student)
                            @php
                                $normalizedGender = strtoupper((string) $student->gender);
                                $genderSortValue = $normalizedGender === 'M'
                                    ? 'male'
                                    : ($normalizedGender === 'F' ? 'female' : strtolower((string) $student->gender));
                            @endphp
                            <tr class="student-row"
                                data-name="{{ strtolower(trim((string) $student->first_name . ' ' . (string) $student->last_name)) }}"
                                data-firstname="{{ strtolower((string) $student->first_name) }}"
                                data-lastname="{{ strtolower((string) $student->last_name) }}"
                                data-gender="{{ strtolower((string) $student->gender) }}"
                                data-gender-sort="{{ $genderSortValue }}" data-index="{{ $index }}"
                                style="{{ $student->type && $student->type->color ? 'border-left: 4px solid ' . $student->type->color . '; background-color: ' . $student->type->color . '10;' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @php
                                        $manualEntry = $student
                                            ->manualAttendanceEntries()
                                            ->where('term_id', $termId)
                                            ->first();
                                        $hasEntry = $manualEntry && $manualEntry->days_absent !== null;
                                    @endphp
                                    <a href="{{ route('attendance.get-manual-entry-form', ['studentId' => $student->id, 'studentIds' => implode(',', $studentIds), 'index' => $index]) }}"
                                        class="manual-entry-link {{ $hasEntry ? 'has-entry' : '' }}"
                                        data-student-id="{{ $student->id }}"
                                        data-bs-toggle="tooltip" title="Manual Attendance Entry">
                                        <i class="fas fa-clock" style="font-size: 14px;"></i>
                                    </a>
                                </td>
                                <td>
                                    {{ $student->first_name }}
                                    @if ($student->type)
                                        <span class="student-type-badge"
                                            style="background-color: {{ $student->type->color ?? '#6c757d' }};">
                                            {{ $student->type->type }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $student->last_name }}</td>
                                <td>
                                    @if ($student->gender == 'M')
                                        <span class="gender-male"><i class="bx bx-male-sign"></i> Male</span>
                                    @else
                                        <span class="gender-female"><i class="bx bx-female-sign"></i> Female</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted">
                                        {{ $student->absentDaysCount() }} / {{ $totalDays - $totalHolidayDays }}
                                    </span>
                                </td>
                                @foreach ($daysOfWeek as $index => $dayOfWeek)
                                    @if ($hasWeekDivider && $index === $weekOneCount)
                                        <td class="week-divider-cell"></td>
                                    @endif
                                    @php
                                        $attendanceValue = isset($attendanceRecords[$dayOfWeek['date']][$student->id])
                                            ? $attendanceRecords[$dayOfWeek['date']][$student->id]->status
                                            : '';
                                    @endphp
                                    <td
                                        class="text-center {{ $dayOfWeek['weekClass'] }} {{ $dayOfWeek['positionClass'] }} {{ $dayOfWeek['date'] === \Carbon\Carbon::today()->toDateString() ? 'today-column' : '' }}">
                                        <input type="text"
                                            name="attendance[{{ $student->id }}][{{ $dayOfWeek['date'] }}]"
                                            class="attendance-input day-{{ $dayOfWeek['date'] }}"
                                            value="{{ $attendanceValue }}" readonly>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr id="no-students-row">
                                <td colspan="{{ $tableColumnCount }}">
                                    <div class="text-center text-muted" style="padding: 40px 0;">
                                        <i class="fas fa-user-graduate" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0" style="font-size: 15px;">No Students in this class</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-container">
                <div class="pagination-info">
                    Showing <span id="showing-from">1</span> to <span id="showing-to">{{ $totalStudents }}</span> of
                    <span id="total-count">{{ $totalStudents }}</span> students
                </div>
                <nav id="pagination-nav">
                    <!-- Pagination will be inserted here by JavaScript if needed -->
                </nav>
            </div>

            <div class="d-flex justify-content-end mt-3">
                @can('class-teacher', $klass)
                    @if (!session('is_past_term'))
                        <button type="submit" class="btn-save-attendance" id="saveAttendanceBtnBottom">
                            <span class="btn-text"><i class="fas fa-save me-1"></i> Save Attendance</span>
                            <span class="btn-spinner">
                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                    aria-hidden="true"></span>
                                Saving...
                            </span>
                        </button>
                    @endif
                @endcan
            </div>

        </form>
    </div>
@else
    <div class="text-center text-muted py-5">
        <i class="fas fa-chalkboard fa-3x mb-3 opacity-50"></i>
        <p>Select a class to view and manage attendance.</p>
    </div>
@endif

<script>
    var currentWeekStart = '{{ $currentWeekStart }}';
    var canGoPrevious = @json($canGoPrevious);
    var canGoNext = @json($canGoNext);

    // Update week navigation button states
    function updateWeekNavButtons() {
        $('#prevWeek').toggleClass('disabled', !canGoPrevious).prop('disabled', !canGoPrevious);
        $('#nextWeek').toggleClass('disabled', !canGoNext).prop('disabled', !canGoNext);
    }

    // Navigate to previous/next week
    function navigateWeek(direction) {
        var classId = '{{ $klass->id }}';
        var termId = '{{ $termId }}';

        $.ajax({
            url: "{{ route('attendance.navigate-week') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                currentWeekStart: currentWeekStart,
                direction: direction,
                classId: classId,
                is_ajax: true
            },
            success: function(response) {
                if (response.success) {
                    // Redirect to the new week
                    var newUrl =
                        "{{ route('attendance.class-list', ['classId' => $klass->id, 'termId' => $termId]) }}/" +
                        response.newWeekStart;
                    window.location.href = newUrl;
                }
            },
            error: function(xhr, status, error) {
                console.error('Error navigating week:', error);
            }
        });
    }

    // Make functions globally accessible for AJAX callback
    window.initAttendanceView = function() {
        applyAllAttendanceColors();
        updateWeekNavButtons();
    };

    // Initialize when DOM is ready (for direct page loads)
    $(document).ready(function() {
        // Initialize on page load
        initAttendanceView();
        // Week navigation click handlers
        $('#prevWeek').on('click', function() {
            if (!$(this).hasClass('disabled')) {
                navigateWeek(-1);
            }
        });

        $('#nextWeek').on('click', function() {
            if (!$(this).hasClass('disabled')) {
                navigateWeek(1);
            }
        });

        // Apply color when input changes (uses parent's applyAttendanceColor function)
        $(document).on('change input', '.attendance-input', function() {
            applyAttendanceColor(this);
        });

        // Note: Click handler for toggling attendance codes is in parent page (index.blade.php)

        // Form submit with loading animation
        $('#attendanceForm').on('submit', function(e) {
            var submitBtns = $('.btn-save-attendance');
            submitBtns.addClass('loading').prop('disabled', true);
        });

        // Client-side sorting and pagination
        const studentTableBody = document.querySelector('#studentTable tbody');
        const manualEntryUrlTemplate = "{{ route('attendance.get-manual-entry-form', ['studentId' => '__STUDENT_ID__', 'studentIds' => '__STUDENT_IDS__', 'index' => '__INDEX__']) }}";
        let currentPage = 1;
        const itemsPerPage = 50;
        let currentSort = {
            column: null,
            direction: 'asc'
        };

        function buildManualEntryUrl(studentId, studentIds, index) {
            return manualEntryUrlTemplate
                .replace('__STUDENT_ID__', String(studentId))
                .replace('__STUDENT_IDS__', studentIds.join(','))
                .replace('__INDEX__', String(index));
        }

        function compareStudentRows(a, b, column) {
            const getValue = (row, key) => row.dataset[key] || '';
            let comparison = getValue(a, column).localeCompare(getValue(b, column), undefined, {
                sensitivity: 'base'
            });

            if (comparison === 0 && column !== 'lastname') {
                comparison = getValue(a, 'lastname').localeCompare(getValue(b, 'lastname'), undefined, {
                    sensitivity: 'base'
                });
            }

            if (comparison === 0 && column !== 'firstname') {
                comparison = getValue(a, 'firstname').localeCompare(getValue(b, 'firstname'), undefined, {
                    sensitivity: 'base'
                });
            }

            if (comparison === 0) {
                comparison = Number(a.dataset.index || 0) - Number(b.dataset.index || 0);
            }

            return currentSort.direction === 'desc' ? -comparison : comparison;
        }

        function updateRowNumbers(rows) {
            rows.forEach((row, index) => {
                const numberCell = row.children[0];
                if (numberCell) {
                    numberCell.textContent = index + 1;
                }
            });
        }

        function updateManualEntryLinks(rows) {
            const orderedStudentIds = rows
                .map(row => row.querySelector('.manual-entry-link')?.dataset.studentId || '')
                .filter(studentId => studentId !== '');

            rows.forEach((row, index) => {
                const manualEntryLink = row.querySelector('.manual-entry-link');
                if (!manualEntryLink) {
                    return;
                }

                manualEntryLink.href = buildManualEntryUrl(
                    manualEntryLink.dataset.studentId,
                    orderedStudentIds,
                    index
                );
            });
        }

        function filterAndSortStudents(resetPage = true) {
            if (resetPage) currentPage = 1;

            const allRows = Array.from(document.querySelectorAll('.student-row'));
            let filteredRows = [...allRows];

            // Sort rows if a sort column is selected
            if (currentSort.column) {
                filteredRows.sort((a, b) => compareStudentRows(a, b, currentSort.column));
            }

            if (studentTableBody && filteredRows.length > 0) {
                filteredRows.forEach(row => studentTableBody.appendChild(row));
            }

            updateManualEntryLinks(filteredRows);
            updateRowNumbers(filteredRows);

            // Calculate pagination
            const totalFiltered = filteredRows.length;
            const totalPages = totalFiltered === 0 ? 0 : Math.ceil(totalFiltered / itemsPerPage);
            if (totalPages > 0 && currentPage > totalPages) {
                currentPage = totalPages;
            }
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;

            // Show/hide based on pagination
            allRows.forEach(row => row.style.display = 'none');
            filteredRows.forEach((row, index) => {
                if (index >= startIndex && index < endIndex) {
                    row.style.display = '';
                }
            });

            // Update showing info
            const showingFrom = totalFiltered > 0 ? startIndex + 1 : 0;
            const showingTo = Math.min(endIndex, totalFiltered);
            document.getElementById('showing-from').textContent = showingFrom;
            document.getElementById('showing-to').textContent = showingTo;
            document.getElementById('total-count').textContent = totalFiltered;

            // Generate pagination if needed
            generatePagination(totalPages, currentPage);
        }

        function generatePagination(totalPages, current) {
            const paginationNav = document.getElementById('pagination-nav');

            if (totalPages <= 1) {
                paginationNav.innerHTML = '';
                return;
            }

            let html = '<ul class="pagination mb-0">';

            // Previous button
            html += `<li class="page-item ${current === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${current - 1}); return false;">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>`;

            // Page numbers
            const maxVisible = 5;
            let startPage = Math.max(1, current - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);

            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `<li class="page-item ${i === current ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a>
                </li>`;
            }

            // Next button
            html += `<li class="page-item ${current === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${current + 1}); return false;">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>`;

            html += '</ul>';
            paginationNav.innerHTML = html;
        }

        function handleSort(column) {
            if (currentSort.column === column) {
                // Toggle direction if same column
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                // New column, default to ascending
                currentSort.column = column;
                currentSort.direction = 'asc';
            }
            updateSortIndicators();
            filterAndSortStudents(true);
        }

        function updateSortIndicators() {
            // Remove all sort classes
            document.querySelectorAll('.sortable').forEach(th => {
                th.classList.remove('asc', 'desc');
            });

            // Add current sort class
            if (currentSort.column) {
                const activeHeader = document.querySelector(`.sortable[data-sort="${currentSort.column}"]`);
                if (activeHeader) {
                    activeHeader.classList.add(currentSort.direction);
                }
            }
        }

        // Make goToPage global for onclick handlers
        window.goToPage = function(page) {
            const totalRows = document.querySelectorAll('.student-row').length;
            const totalPages = totalRows === 0 ? 1 : Math.ceil(totalRows / itemsPerPage);
            currentPage = Math.min(Math.max(page, 1), totalPages);
            filterAndSortStudents(false);
        };

        // Sortable headers click
        document.querySelectorAll('.sortable').forEach(th => {
            th.addEventListener('click', () => {
                handleSort(th.dataset.sort);
            });
        });

        // Initialize
        filterAndSortStudents(true);

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

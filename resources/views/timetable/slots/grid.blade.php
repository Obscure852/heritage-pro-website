@extends('layouts.master')
@section('title')
    Timetable Grid
@endsection
@section('css')
    <style>
        .grid-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .grid-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .grid-body {
            padding: 24px;
        }

        .grade-selector {
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .grade-selector label {
            font-weight: 600;
            color: #374151;
            white-space: nowrap;
        }

        .grade-selector select {
            max-width: 240px;
        }

        /* Timetable Grid Table */
        .timetable-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .timetable-grid th {
            padding: 6px 8px;
            font-size: 12px;
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            text-align: center;
            white-space: nowrap;
        }

        .timetable-grid td {
            padding: 4px 6px;
            border: 1px solid #e5e7eb;
            text-align: center;
            min-width: 80px;
            height: 48px;
            vertical-align: middle;
            font-size: 12px;
        }

        /* Slot cells */
        .slot-cell {
            cursor: pointer;
            transition: all 0.15s;
            position: relative;
        }

        .slot-empty:hover {
            background: #f0f7ff;
        }

        .slot-filled {
            border-left-width: 3px;
        }

        .slot-filled .slot-subject {
            font-weight: 600;
            font-size: 11px;
            line-height: 1.3;
        }

        .slot-filled .slot-teacher {
            font-size: 10px;
            color: #6b7280;
        }

        .slot-filled .slot-coupling {
            margin-top: 2px;
            font-size: 9px;
            font-weight: 600;
            line-height: 1.2;
            color: #1d4ed8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .slot-filled .slot-coupling i {
            margin-right: 2px;
        }

        .slot-filled.slot-coupling-overlap {
            box-shadow: inset 0 0 0 1px #f59e0b;
        }

        .slot-filled .slot-coupling.slot-coupling-overlap-label {
            color: #b45309;
        }

        .slot-filled.slot-double-alignment-issue {
            box-shadow: inset 0 0 0 2px #dc2626;
        }

        .slot-filled.slot-core-elective-overlap-issue {
            box-shadow: inset 0 0 0 2px #f59e0b;
        }

        .slot-filled.slot-coupling-split-issue {
            box-shadow: inset 0 0 0 2px #0ea5e9;
        }

        .slot-filled .integrity-issue-icons {
            position: absolute;
            top: 2px;
            left: 2px;
            font-size: 10px;
            z-index: 2;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .slot-filled .integrity-issue-icons .issue-icon-double {
            color: #dc2626;
        }

        .slot-filled .integrity-issue-icons .issue-icon-core-overlap {
            color: #d97706;
        }

        .slot-filled .integrity-issue-icons .issue-icon-coupling-split {
            color: #0284c7;
        }

        .slot-block {
            /* Multi-period merged cells */
        }

        /* Break column */
        .slot-break {
            background: #fef3c7;
            border-left: 1px dashed #f59e0b;
            border-right: 1px dashed #f59e0b;
            cursor: default;
            min-width: 30px;
            font-size: 10px;
            color: #92400e;
        }

        /* Class separator between class groups */
        .class-separator td {
            border-top: 3px solid #4e73df;
        }

        .class-name-cell {
            font-weight: 700;
            background: #f1f5f9;
            font-size: 13px;
            vertical-align: middle;
            text-align: center;
        }

        .day-label-cell {
            font-weight: 500;
            font-size: 12px;
            background: #fafbfc;
            white-space: nowrap;
        }

        /* Cross-class conflict highlighting */
        .slot-teacher-conflict {
            box-shadow: inset 0 0 0 2px #ef4444;
            position: relative;
        }

        .slot-teacher-conflict::after {
            content: '';
            position: absolute;
            top: 2px;
            right: 2px;
            width: 6px;
            height: 6px;
            background: #ef4444;
            border-radius: 50%;
        }

        .slot-locked {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .lock-toggle {
            position: absolute;
            top: 2px;
            right: 2px;
            font-size: 9px;
            color: #9ca3af;
            cursor: pointer;
            z-index: 2;
            padding: 2px;
            line-height: 1;
        }

        .lock-toggle:hover {
            color: #4e73df;
        }

        .slot-locked .lock-toggle {
            color: #dc2626;
        }

        /* Cell assignment pulse animation */
        @keyframes cellPulse {
            0% {
                background-color: rgba(16, 185, 129, 0.3);
            }

            100% {
                background-color: transparent;
            }
        }

        .cell-just-assigned {
            animation: cellPulse 0.5s ease;
        }

        /* Loading skeleton */
        .grid-loading {
            padding: 40px;
            text-align: center;
            color: #9ca3af;
        }

        .grid-loading .spinner-border {
            width: 2rem;
            height: 2rem;
        }

        .grid-empty {
            padding: 60px 20px;
            text-align: center;
            color: #9ca3af;
        }

        .grid-empty i {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
            opacity: 0.4;
        }

        /* Help text (reuse from period-settings) */
        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
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
            line-height: 1.5;
            margin: 0;
        }

        /* Button loading state */
        .btn-loading .btn-spinner {
            display: none;
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

        /* Allocation status colors */
        .alloc-ok {
            color: #059669;
        }

        .alloc-warn {
            color: #d97706;
        }

        .alloc-over {
            color: #dc2626;
            font-weight: 600;
        }

        /* Drag-and-drop visual feedback */
        .slot-filled[draggable="true"] {
            cursor: grab;
        }

        .slot-filled[draggable="true"]:active {
            cursor: grabbing;
        }

        .slot-filled.dragging {
            opacity: 0.4;
            outline: 2px dashed #4e73df;
        }

        .slot-empty.drag-over {
            background: rgba(78, 115, 223, 0.15) !important;
            outline: 2px solid #4e73df;
            outline-offset: -2px;
        }

        .slot-filled.drag-over-swap {
            outline: 2px solid #f59e0b;
            outline-offset: -2px;
            background-image: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 5px,
                rgba(245, 158, 11, 0.08) 5px,
                rgba(245, 158, 11, 0.08) 10px
            ) !important;
        }

        @media (max-width: 768px) {
            .grid-header {
                padding: 20px;
            }

            .grid-body {
                padding: 16px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('timetable.index') }}">Timetable</a>
        @endslot
        @slot('title')
            Timetable Grid
        @endslot
    @endcomponent

    <div id="messageContainer"></div>

    <div class="row mb-2">
        <div class="col-10"></div>
        <div class="col-2 d-flex align-items-center justify-content-end">
            <select id="gradeSelect" class="form-select">
                <option value="">-- Choose a Grade --</option>
                @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}" data-klasses='@json($grade->klasses->map(fn($k) => ['id' => $k->id, 'name' => $k->name]))'>
                        {{ $grade->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>


    <div class="grid-container">
        <div class="grid-header">
            <h4 class="mb-1 text-white"><i class="bx bx-grid-alt me-2"></i>{{ $timetable->name }} - Slot Management</h4>
            <p class="mb-0 opacity-75">Click empty cells to assign lessons. Click filled cells to remove. Drag lessons between cells to move or swap.</p>
        </div>
        <div class="grid-body">
            {{-- Grid area --}}
            <div id="gridArea">
                <div class="grid-empty">
                    <i class="bx bx-grid-alt"></i>
                    <p>Select a grade to view the timetable grid.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Include assignment modal partial --}}
    @include('timetable.slots._assignment-modal')
@endsection

@section('script')
    <script>
        // =====================================================
        // Variables
        // =====================================================
        var csrfToken = '{{ csrf_token() }}';
        var timetableId = {{ $timetable->id }};
        var gridDataUrl = '{{ route('timetable.slots.grid-data', $timetable->id) }}';
        var assignUrl = '{{ route('timetable.slots.assign') }}';
        var conflictCheckUrl = '{{ route('timetable.slots.check-conflicts') }}';
        var allocationStatusUrl = '{{ route('timetable.slots.allocation-status') }}';
        var teachersUrl = '{{ route('timetable.slots.teachers') }}';
        var subjectsUrl = '{{ route('timetable.slots.subjects') }}';
        var deleteUrlBase = '{{ url('timetable/slots/delete') }}';
        var moveUrl = '{{ route("timetable.slots.move") }}';
        var swapUrl = '{{ route("timetable.slots.swap") }}';
        var toggleLockUrl = '{{ route("timetable.slots.toggle-lock") }}';
        var warningsUrl = '{{ route("timetable.slots.get-warnings") }}';
        var daySchedule = @json($daySchedule);
        var validDoubleStartPeriods = computeValidDoubleStartsFromSchedule(daySchedule);
        var validDoubleStartSet = buildPeriodSet(validDoubleStartPeriods);
        var isDragging = false;

        var currentGridData = {}; // { klassId: { day: { period: slotData } } }
        var assignmentModal = null; // Bootstrap Modal instance
        var currentDay = null;
        var currentPeriod = null;
        var currentKlassId = null;
        var currentKlasses = []; // Klasses for selected grade
        var teachersCache = null;
        var allocationCache = {}; // { klassSubjectId: { planned: {...}, used: {...}, remaining: {...} } }

        // =====================================================
        // Department Color Palette
        // =====================================================
        var DEPARTMENT_COLORS = [
            '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
            '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16',
            '#06b6d4', '#e11d48', '#a855f7', '#22c55e', '#eab308',
            '#0ea5e9', '#d946ef', '#64748b', '#f43f5e', '#2dd4bf'
        ];

        function getDeptColor(deptId) {
            if (!deptId) return '#94a3b8';
            return DEPARTMENT_COLORS[deptId % DEPARTMENT_COLORS.length];
        }

        function getSubjectColor(klassSubjectId) {
            if (!klassSubjectId) return '#94a3b8';
            return DEPARTMENT_COLORS[klassSubjectId % DEPARTMENT_COLORS.length];
        }

        function getCouplingColor(couplingKey) {
            if (!couplingKey) return '#0ea5e9';
            var hash = 0;
            for (var i = 0; i < couplingKey.length; i++) {
                hash = ((hash << 5) - hash) + couplingKey.charCodeAt(i);
                hash |= 0;
            }
            return DEPARTMENT_COLORS[Math.abs(hash) % DEPARTMENT_COLORS.length];
        }

        function getSlotColor(slot) {
            if (slot && slot.coupling_group_key) {
                return getCouplingColor(slot.coupling_group_key);
            }
            return getSubjectColor(slot ? slot.klass_subject_id : null);
        }

        function hexToRgb(hex) {
            var r = parseInt(hex.slice(1, 3), 16);
            var g = parseInt(hex.slice(3, 5), 16);
            var b = parseInt(hex.slice(5, 7), 16);
            return {
                r: r,
                g: g,
                b: b
            };
        }

        function getInitials(name) {
            if (!name) return '?';
            var parts = name.trim().split(/\s+/);
            if (parts.length >= 2) {
                return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
            }
            return parts[0].substring(0, 2).toUpperCase();
        }

        function buildPeriodSet(periods) {
            var set = {};
            (periods || []).forEach(function(period) {
                set[parseInt(period)] = true;
            });
            return set;
        }

        function computeValidDoubleStartsFromSchedule(schedule) {
            var periods = [];
            var breakAfterSet = {};
            var lastPeriodSeen = null;

            (schedule || []).forEach(function(item) {
                if (item.type === 'period') {
                    lastPeriodSeen = parseInt(item.period);
                    periods.push(lastPeriodSeen);
                    return;
                }

                if (item.type === 'break' && lastPeriodSeen !== null) {
                    breakAfterSet[lastPeriodSeen] = true;
                }
            });

            if (periods.length < 2) {
                return [];
            }

            periods = periods.filter(function(value) {
                return Number.isFinite(value) && value > 0;
            });

            if (periods.length < 2) {
                return [];
            }

            periods.sort(function(a, b) {
                return a - b;
            });

            var periodsPerDay = periods[periods.length - 1];
            if (!Number.isFinite(periodsPerDay) || periodsPerDay < 2) {
                return [];
            }

            var normalizedBreaks = Object.keys(breakAfterSet)
                .map(function(value) {
                    return parseInt(value);
                })
                .filter(function(value) {
                    return Number.isFinite(value) && value >= 1 && value < periodsPerDay;
                })
                .sort(function(a, b) {
                    return a - b;
                });

            var segmentStarts = [1];
            normalizedBreaks.forEach(function(afterPeriod) {
                var start = afterPeriod + 1;
                if (start <= periodsPerDay) {
                    segmentStarts.push(start);
                }
            });

            segmentStarts = Array.from(new Set(segmentStarts)).sort(function(a, b) {
                return a - b;
            });

            var validSet = {};
            for (var i = 0; i < segmentStarts.length; i++) {
                var segmentStart = segmentStarts[i];
                var segmentEnd = (i + 1 < segmentStarts.length)
                    ? segmentStarts[i + 1] - 1
                    : periodsPerDay;

                for (var start = segmentStart; start + 1 <= segmentEnd; start += 2) {
                    validSet[start] = true;
                }
            }

            return Object.keys(validSet).map(function(value) {
                return parseInt(value);
            }).sort(function(a, b) {
                return a - b;
            });
        }

        function isValidDoubleStart(period) {
            return !!validDoubleStartSet[parseInt(period)];
        }

        function getDoubleAlignmentWarningText(period) {
            var starts = validDoubleStartPeriods.length ? validDoubleStartPeriods.join(', ') : 'none';
            return 'Double periods must start at aligned periods (' + starts + '). Period ' + period + ' is not a valid double start.';
        }

        function showBlockTypeWarning(message, blocking) {
            var warningDiv = document.getElementById('blockTypeWarning');
            warningDiv.textContent = message;
            warningDiv.dataset.blocking = blocking ? '1' : '0';
            warningDiv.classList.remove('text-danger', 'text-warning');
            warningDiv.classList.add(blocking ? 'text-danger' : 'text-warning');
            warningDiv.style.display = 'block';
        }

        function hideBlockTypeWarning() {
            var warningDiv = document.getElementById('blockTypeWarning');
            warningDiv.dataset.blocking = '0';
            warningDiv.style.display = 'none';
        }

        function getCouplingInfoHtml(slot) {
            if (!slot || !slot.has_optional_overlay) return '';

            var count = parseInt(slot.coupled_optional_count || 0);
            if (count <= 0) return '';

            var label = slot.coupling_group_label || 'Coupled Electives';
            var subjects = Array.isArray(slot.coupled_optional_subjects) ? slot.coupled_optional_subjects.join(', ') : '';
            var tooltip = label + ' (' + count + ')' + (subjects ? ': ' + subjects : '');
            var overlapClass = slot.has_core_overlap ? ' slot-coupling-overlap-label' : '';

            return '<div class="slot-coupling' + overlapClass + '" title="' + escapeHtml(tooltip) + '">' +
                '<i class="fas fa-link"></i>' + escapeHtml(label) + ' (' + count + ')' +
                '</div>';
        }

        function buildSlotTitle(slot, subjectName, duration) {
            var title = (slot.teacher_name || 'Unknown') + ' - ' + subjectName;
            if (duration > 1) {
                title += ' (' + getBlockLabel(duration) + ')';
            }

            if (slot && slot.has_optional_overlay) {
                var label = slot.coupling_group_label || 'Coupled electives';
                var subjects = Array.isArray(slot.coupled_optional_subjects) ? slot.coupled_optional_subjects.join(', ') : '';
                if (subjects) {
                    title += ' | ' + label + ': ' + subjects;
                }
            }

            if (slot && Array.isArray(slot.issue_reasons) && slot.issue_reasons.length > 0) {
                title += ' | Issues: ' + slot.issue_reasons.join('; ');
            } else if (slot && slot.has_double_alignment_issue) {
                title += ' | Warning: this double starts at a misaligned period.';
            }

            return escapeHtml(title);
        }

        function buildIntegrityIssueIcons(slot) {
            if (!slot) return '';

            var icons = [];
            if (slot.has_double_alignment_issue) {
                icons.push('<i class="fas fa-exclamation-triangle issue-icon-double" title="Double alignment issue"></i>');
            }
            if (slot.has_core_elective_overlap) {
                icons.push('<i class="fas fa-exclamation-circle issue-icon-core-overlap" title="Core/elective overlap issue"></i>');
            }
            if (slot.has_coupling_split_issue) {
                icons.push('<i class="fas fa-link issue-icon-coupling-split" title="Coupling split issue"></i>');
            }

            if (icons.length === 0) return '';
            return '<span class="integrity-issue-icons">' + icons.join('') + '</span>';
        }

        // =====================================================
        // DOMContentLoaded
        // =====================================================
        document.addEventListener('DOMContentLoaded', function() {
            assignmentModal = new bootstrap.Modal(document.getElementById('assignmentModal'));

            // Grade selector
            var gradeSelect = document.getElementById('gradeSelect');
            gradeSelect.addEventListener('change', function() {
                var gradeId = this.value;
                if (!gradeId) {
                    document.getElementById('gridArea').innerHTML =
                        '<div class="grid-empty">' +
                        '<i class="bx bx-grid-alt"></i>' +
                        '<p>Select a grade to view the timetable grid.</p>' +
                        '</div>';
                    return;
                }
                var option = this.options[this.selectedIndex];
                try {
                    currentKlasses = JSON.parse(option.dataset.klasses || '[]');
                } catch (e) {
                    currentKlasses = [];
                }
                loadGridData();
            });

            // Subject change
            document.getElementById('modalSubjectSelect').addEventListener('change', onSubjectChange);

            // Teacher change
            document.getElementById('modalTeacherSelect').addEventListener('change', onTeacherChange);

            // Block type change
            var blockRadios = document.querySelectorAll('input[name="block_type"]');
            blockRadios.forEach(function(radio) {
                radio.addEventListener('change', onBlockTypeChange);
            });

            // Save slot button
            document.getElementById('saveSlotBtn').addEventListener('click', saveSlotAssignment);

            // Auto-select first grade on page load
            if (gradeSelect.options.length > 1) {
                gradeSelect.selectedIndex = 1;
                gradeSelect.dispatchEvent(new Event('change'));
            }

            // Event delegation for grid cell clicks
            document.getElementById('gridArea').addEventListener('click', handleCellClick);

            // Drag-and-drop event delegation
            var gridArea = document.getElementById('gridArea');
            gridArea.addEventListener('dragstart', handleDragStart);
            gridArea.addEventListener('dragover', handleDragOver);
            gridArea.addEventListener('dragleave', handleDragLeave);
            gridArea.addEventListener('drop', handleDrop);
            gridArea.addEventListener('dragend', handleDragEnd);
        });

        // =====================================================
        // Load Grid Data (per klass, then assemble)
        // =====================================================
        function loadGridData() {
            if (currentKlasses.length === 0) {
                document.getElementById('gridArea').innerHTML =
                    '<div class="grid-empty">' +
                    '<i class="bx bx-info-circle"></i>' +
                    '<p>No classes found for this grade.</p>' +
                    '</div>';
                return;
            }

            document.getElementById('gridArea').innerHTML =
                '<div class="grid-loading">' +
                '<div class="spinner-border text-primary" role="status"></div>' +
                '<p class="mt-2">Loading timetable grid...</p>' +
                '</div>';

            currentGridData = {};
            var loaded = 0;
            var total = currentKlasses.length;
            var hasError = false;

            currentKlasses.forEach(function(klass) {
                fetch(gridDataUrl + '?klass_id=' + klass.id, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            currentGridData[klass.id] = data.grid || {};
                        }
                        loaded++;
                        if (loaded === total && !hasError) {
                            loadAllocationData();
                        }
                    })
                    .catch(function(error) {
                        console.error('Error loading grid for class ' + klass.id + ':', error);
                        hasError = true;
                        loaded++;
                        if (loaded === total) {
                            displayMessage('Error loading timetable grid data.', 'error');
                            renderGrid();
                        }
                    });
            });
        }

        function loadAllocationData() {
            fetch(allocationStatusUrl + '?timetable_id=' + timetableId, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        allocationCache = data.allocations || {};
                    }
                    renderGrid();
                })
                .catch(function(error) {
                    console.error('Error loading allocation data:', error);
                    renderGrid();
                });
        }

        // =====================================================
        // Render Grid
        // =====================================================
        function renderGrid() {
            if (!daySchedule || daySchedule.length === 0) {
                document.getElementById('gridArea').innerHTML =
                    '<div class="grid-empty">' +
                    '<i class="bx bx-time-five"></i>' +
                    '<p>No period settings configured. Please set up periods first.</p>' +
                    '</div>';
                return;
            }

            var html = '<div class="table-responsive"><table class="timetable-grid">';

            // Header row: empty (class) | empty (day) | period/break columns
            html += '<thead><tr>';
            html += '<th style="min-width: 60px;">Class</th>';
            html += '<th style="min-width: 50px;">Day</th>';

            for (var s = 0; s < daySchedule.length; s++) {
                var item = daySchedule[s];
                if (item.type === 'period') {
                    html += '<th>P' + item.period + '<br><span style="font-size:9px;">' +
                        item.start_time + '-' + item.end_time + '</span></th>';
                } else if (item.type === 'break') {
                    html += '<th class="slot-break">' + (item.label || 'Break') + '</th>';
                }
            }
            html += '</tr></thead>';

            // Body: for each klass, 6 day rows
            html += '<tbody>';
            for (var ki = 0; ki < currentKlasses.length; ki++) {
                var klass = currentKlasses[ki];
                var klassGrid = currentGridData[klass.id] || {};
                var separatorClass = (ki > 0) ? ' class="class-separator"' : '';

                for (var day = 1; day <= 6; day++) {
                    var dayData = klassGrid[day] || {};
                    var rowSeparator = (day === 1 && ki > 0) ? ' class="class-separator"' : '';
                    html += '<tr' + rowSeparator + '>';

                    // Class name cell (rowspan 6, only on first day)
                    if (day === 1) {
                        html += '<td class="class-name-cell" rowspan="6">' + escapeHtml(klass.name) + '</td>';
                    }

                    // Day label
                    html += '<td class="day-label-cell">Day ' + day + '</td>';

                    // Period/break cells
                    var skipUntil = 0;
                    for (var si = 0; si < daySchedule.length; si++) {
                        var schedItem = daySchedule[si];

                        if (schedItem.type === 'break') {
                            html += '<td class="slot-break"></td>';
                            continue;
                        }

                        // It's a period
                        var period = parseInt(schedItem.period);

                        if (period < skipUntil) {
                            // This period is covered by a multi-period block colspan
                            continue;
                        }

                        var slot = dayData[period];

                        if (slot) {
                            var duration = slot.duration || 1;
                            var color = getSlotColor(slot);
                            var rgb = hexToRgb(color);
                            var initials = getInitials(slot.teacher_name);
                            var subjectName = slot.subject_name || 'Unknown';
                            var couplingInfoHtml = getCouplingInfoHtml(slot);
                            var cellTitle = buildSlotTitle(slot, subjectName, duration);
                            var overlapClass = slot.has_core_overlap ? ' slot-coupling-overlap' : '';
                            var alignmentIssueClass = slot.has_double_alignment_issue ? ' slot-double-alignment-issue' : '';
                            var coreElectiveIssueClass = slot.has_core_elective_overlap ? ' slot-core-elective-overlap-issue' : '';
                            var couplingSplitIssueClass = slot.has_coupling_split_issue ? ' slot-coupling-split-issue' : '';
                            var issueIconHtml = buildIntegrityIssueIcons(slot);
                            var subjectDisplay = abbreviate(subjectName);
                            if (slot.has_optional_overlay && !slot.klass_subject_id && parseInt(slot.coupled_optional_count || 0) > 1) {
                                subjectDisplay = 'Electives';
                            }

                            if (duration > 1 && slot.block_id) {
                                // Multi-period block: check if this is the first slot of the block
                                // Only render if current period is the slot's period_number
                                var actualColspan = getActualColspan(si, duration);
                                var blockIsLocked = slot.is_locked || false;
                                var blockDraggableAttr = (blockIsLocked || slot.is_optional) ? '' : ' draggable="true"';
                                var blockLockedClass = blockIsLocked ? ' slot-locked' : '';
                                var blockLockIcon = blockIsLocked ? 'fa-lock' : 'fa-lock-open';
                                var blockLockTitle = blockIsLocked ? 'Click to unlock' : 'Click to lock';
                                html += '<td class="slot-cell slot-filled slot-block' + blockLockedClass + overlapClass + alignmentIssueClass + coreElectiveIssueClass + couplingSplitIssueClass + '"' + blockDraggableAttr + ' ' +
                                    'colspan="' + actualColspan + '" ' +
                                    'style="background-color: rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b +
                                    ', 0.12); border-left: 3px solid ' + color + ';" ' +
                                    'data-slot-id="' + slot.id + '" ' +
                                    'data-klass-id="' + klass.id + '" ' +
                                    'data-day="' + day + '" ' +
                                    'data-period="' + period + '" ' +
                                    'data-block-id="' + (slot.block_id || '') + '" ' +
                                    'data-block-size="' + duration + '" ' +
                                    'data-teacher-id="' + (slot.teacher_id || '') + '" ' +
                                    'data-bs-toggle="tooltip" ' +
                                    'title="' + cellTitle + '">' +
                                    issueIconHtml +
                                    '<span class="lock-toggle" data-slot-id="' + slot.id + '" title="' + blockLockTitle + '">' +
                                    '<i class="fas ' + blockLockIcon + '"></i></span>' +
                                    '<div class="slot-subject">' + escapeHtml(subjectDisplay) + ' (' +
                                    getBlockLabel(duration) + ')</div>' +
                                    couplingInfoHtml +
                                    '<div class="slot-teacher">' + escapeHtml(initials) + '</div>' +
                                    '</td>';
                                skipUntil = period + duration;
                            } else {
                                // Single slot
                                var isLocked = slot.is_locked || false;
                                var draggableAttr = (isLocked || slot.is_optional) ? '' : ' draggable="true"';
                                var lockedClass = isLocked ? ' slot-locked' : '';
                                var lockIcon = isLocked ? 'fa-lock' : 'fa-lock-open';
                                var lockTitle = isLocked ? 'Click to unlock' : 'Click to lock';
                                html += '<td class="slot-cell slot-filled' + lockedClass + overlapClass + alignmentIssueClass + coreElectiveIssueClass + couplingSplitIssueClass + '"' + draggableAttr + ' ' +
                                    'style="background-color: rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b +
                                    ', 0.12); border-left: 3px solid ' + color + ';" ' +
                                    'data-slot-id="' + slot.id + '" ' +
                                    'data-klass-id="' + klass.id + '" ' +
                                    'data-day="' + day + '" ' +
                                    'data-period="' + period + '" ' +
                                    'data-teacher-id="' + (slot.teacher_id || '') + '" ' +
                                    'data-bs-toggle="tooltip" ' +
                                    'title="' + cellTitle + '">' +
                                    issueIconHtml +
                                    '<span class="lock-toggle" data-slot-id="' + slot.id + '" title="' + lockTitle + '">' +
                                    '<i class="fas ' + lockIcon + '"></i></span>' +
                                    '<div class="slot-subject">' + escapeHtml(subjectDisplay) + '</div>' +
                                    couplingInfoHtml +
                                    '<div class="slot-teacher">' + escapeHtml(initials) + '</div>' +
                                    '</td>';
                            }
                        } else {
                            // Empty cell - clickable
                            html += '<td class="slot-cell slot-empty" ' +
                                'data-klass-id="' + klass.id + '" ' +
                                'data-day="' + day + '" ' +
                                'data-period="' + period + '"></td>';
                        }
                    }

                    html += '</tr>';
                }
            }

            html += '</tbody></table></div>';
            document.getElementById('gridArea').innerHTML = html;

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function(el) {
                new bootstrap.Tooltip(el);
            });
        }

        /**
         * Calculate actual colspan considering break columns in between.
         * A multi-period block of duration N covers N periods, but we need
         * to also include any break columns between those periods.
         */
        function getActualColspan(scheduleIndex, duration) {
            var colspan = 0;
            var periodsConsumed = 0;
            for (var i = scheduleIndex; i < daySchedule.length && periodsConsumed < duration; i++) {
                colspan++;
                if (daySchedule[i].type === 'period') {
                    periodsConsumed++;
                }
            }
            return colspan;
        }

        function getBlockLabel(duration) {
            if (duration === 2) return 'Dbl';
            if (duration === 3) return 'Trp';
            return 'Sgl';
        }

        function abbreviate(name) {
            if (!name) return '?';
            if (name.length <= 8) return name;
            return name.substring(0, 7) + '.';
        }

        function escapeHtml(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }

        // =====================================================
        // Cell Click Handler (Event Delegation)
        // =====================================================
        function handleCellClick(event) {
            if (isDragging) {
                isDragging = false;
                return;
            }

            // Lock toggle intercept -- prevent cell click (delete) when clicking lock icon
            var lockToggle = event.target.closest('.lock-toggle');
            if (lockToggle) {
                event.stopPropagation();
                var slotId = parseInt(lockToggle.dataset.slotId);
                toggleSlotLock(slotId);
                return;
            }

            var emptyCell = event.target.closest('.slot-empty');
            var filledCell = event.target.closest('.slot-filled');

            if (emptyCell) {
                openAssignmentModal(emptyCell);
            } else if (filledCell) {
                confirmDeleteSlot(filledCell);
            }
        }

        // =====================================================
        // Toggle Slot Lock (AJAX)
        // =====================================================
        function toggleSlotLock(slotId) {
            fetch(toggleLockUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ slot_id: slotId })
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    var label = data.is_locked ? 'locked' : 'unlocked';
                    displayMessage('Slot ' + label + ' successfully (' + data.count + ' slot(s)).');
                    loadGridData();
                } else {
                    displayMessage(data.message || 'Error toggling lock.', 'error');
                }
            })
            .catch(function(error) {
                console.error('Error toggling lock:', error);
                displayMessage('An error occurred while toggling the lock.', 'error');
            });
        }

        // =====================================================
        // Open Assignment Modal (empty cell click)
        // =====================================================
        function openAssignmentModal(cell) {
            var klassId = cell.dataset.klassId;
            var day = parseInt(cell.dataset.day);
            var period = parseInt(cell.dataset.period);

            currentKlassId = parseInt(klassId);
            currentDay = day;
            currentPeriod = period;

            // Set hidden fields
            document.getElementById('modalTimetableId').value = timetableId;
            document.getElementById('modalKlassId').value = klassId;
            document.getElementById('modalDayOfCycle').value = day;
            document.getElementById('modalPeriodNumber').value = period;

            // Find class name and period time for title
            var className = '';
            for (var i = 0; i < currentKlasses.length; i++) {
                if (currentKlasses[i].id == klassId) {
                    className = currentKlasses[i].name;
                    break;
                }
            }

            var periodTime = '';
            for (var s = 0; s < daySchedule.length; s++) {
                if (daySchedule[s].type === 'period' && parseInt(daySchedule[s].period) === period) {
                    periodTime = daySchedule[s].start_time + '-' + daySchedule[s].end_time;
                    break;
                }
            }

            document.getElementById('modalTitle').textContent =
                'Assign Lesson: ' + className + ' - Day ' + day + ', Period ' + period +
                (periodTime ? ' (' + periodTime + ')' : '');

            // Reset form
            document.getElementById('modalSubjectSelect').innerHTML = '<option value="">-- Select Subject --</option>';
            document.getElementById('modalTeacherSelect').innerHTML = '<option value="">-- Select Teacher --</option>';
            document.getElementById('allocationStatus').style.display = 'none';
            document.getElementById('conflictIndicator').style.display = 'none';
            document.getElementById('softWarningIndicator').style.display = 'none';
            hideBlockTypeWarning();
            document.querySelector('input[name="block_type"][value="1"]').checked = true;
            document.getElementById('saveSlotBtn').disabled = true;

            // Disable block sizes that would span a break or overlap occupied slots
            var maxBlock = getMaxBlockSize(period, currentKlassId, currentDay);
            var doubleAllowedAtStart = isValidDoubleStart(period);
            [1, 2, 3].forEach(function(size) {
                var radio = document.querySelector('input[name="block_type"][value="' + size + '"]');
                var label = radio.closest('.form-check');
                var disabledReason = '';

                if (size > maxBlock) {
                    disabledReason = 'Not enough periods before next break';
                } else if (size === 2 && !doubleAllowedAtStart) {
                    disabledReason = getDoubleAlignmentWarningText(period);
                }

                if (disabledReason) {
                    radio.disabled = true;
                    label.style.opacity = '0.4';
                    label.title = disabledReason;
                } else {
                    radio.disabled = false;
                    label.style.opacity = '1';
                    label.title = '';
                }
            });

            var selectedBlock = document.querySelector('input[name="block_type"]:checked');
            if (selectedBlock && selectedBlock.disabled) {
                document.querySelector('input[name="block_type"][value="1"]').checked = true;
            }

            if (!doubleAllowedAtStart) {
                showBlockTypeWarning(getDoubleAlignmentWarningText(period), false);
            }

            // Load subjects for this class
            loadSubjectsForClass(klassId);

            // Load teachers (cached)
            loadTeachers();

            // Show modal
            assignmentModal.show();
        }

        // =====================================================
        // Load Subjects for a Class
        // =====================================================
        function loadSubjectsForClass(klassId) {
            var select = document.getElementById('modalSubjectSelect');
            select.innerHTML = '<option value="">Loading...</option>';

            fetch(subjectsUrl + '?klass_id=' + klassId, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    select.innerHTML = '<option value="">-- Select Subject --</option>';
                    if (data.success && data.subjects) {
                        data.subjects.forEach(function(subj) {
                            var opt = document.createElement('option');
                            opt.value = subj.id;
                            opt.textContent = subj.subject_name || 'Unknown';
                            opt.dataset.type = subj.type || '';
                            opt.dataset.teacherId = subj.teacher_id || '';
                            opt.dataset.teacherName = subj.teacher_name || '';
                            opt.dataset.subjectName = subj.subject_name || '';
                            select.appendChild(opt);
                        });
                    }
                })
                .catch(function(error) {
                    console.error('Error loading subjects:', error);
                    select.innerHTML = '<option value="">-- Select Subject --</option>';
                });
        }

        // =====================================================
        // Load Teachers (cached)
        // =====================================================
        function loadTeachers() {
            var select = document.getElementById('modalTeacherSelect');

            if (teachersCache) {
                populateTeacherSelect(select, teachersCache);
                return;
            }

            select.innerHTML = '<option value="">Loading...</option>';

            fetch(teachersUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success && data.teachers) {
                        teachersCache = data.teachers;
                        populateTeacherSelect(select, teachersCache);
                    } else {
                        select.innerHTML = '<option value="">-- Select Teacher --</option>';
                    }
                })
                .catch(function(error) {
                    console.error('Error loading teachers:', error);
                    select.innerHTML = '<option value="">-- Select Teacher --</option>';
                });
        }

        function populateTeacherSelect(select, teachers) {
            select.innerHTML = '<option value="">-- Select Teacher --</option>';
            teachers.forEach(function(teacher) {
                var opt = document.createElement('option');
                opt.value = teacher.id;
                opt.textContent = teacher.name;
                select.appendChild(opt);
            });
        }

        // =====================================================
        // Subject Change -> Allocation Status
        // =====================================================
        function onSubjectChange() {
            var subjectSelect = document.getElementById('modalSubjectSelect');
            var subjectId = subjectSelect.value;
            var statusDiv = document.getElementById('allocationStatus');
            var contentDiv = document.getElementById('allocationStatusContent');

            if (!subjectId) {
                statusDiv.style.display = 'none';
                updateSaveButtonState();
                return;
            }

            // Auto-select teacher if the subject has a pre-assigned one
            var selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
            var preAssignedTeacherId = selectedOption.dataset.teacherId;
            if (preAssignedTeacherId) {
                var teacherSelect = document.getElementById('modalTeacherSelect');
                if (teacherSelect.querySelector('option[value="' + preAssignedTeacherId + '"]')) {
                    teacherSelect.value = preAssignedTeacherId;
                }
            }

            // Check allocation cache
            var allocation = allocationCache[subjectId];
            if (allocation) {
                displayAllocationStatus(allocation, contentDiv, statusDiv);
            } else {
                // Fetch allocation for this specific class-subject
                fetch(allocationStatusUrl + '?timetable_id=' + timetableId + '&klass_id=' + currentKlassId, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success && data.allocations) {
                            // Merge into cache
                            for (var key in data.allocations) {
                                allocationCache[key] = data.allocations[key];
                            }
                            allocation = allocationCache[subjectId];
                            if (allocation) {
                                displayAllocationStatus(allocation, contentDiv, statusDiv);
                            } else {
                                contentDiv.innerHTML =
                                    '<span class="text-muted">No block allocations configured for this subject.</span>';
                                statusDiv.style.display = 'block';
                            }
                        } else {
                            statusDiv.style.display = 'none';
                        }
                    })
                    .catch(function(error) {
                        console.error('Error loading allocation status:', error);
                        statusDiv.style.display = 'none';
                    });
            }

            // If teacher already selected, re-check conflicts
            if (document.getElementById('modalTeacherSelect').value) {
                checkConflictsRealTime();
            }

            updateSaveButtonState();
        }

        function displayAllocationStatus(allocation, contentDiv, statusDiv) {
            var planned = allocation.planned || {
                singles: 0,
                doubles: 0,
                triples: 0
            };
            var used = allocation.used || {
                singles: 0,
                doubles: 0,
                triples: 0
            };
            var remaining = allocation.remaining || {
                singles: 0,
                doubles: 0,
                triples: 0
            };

            var parts = [];
            parts.push(formatAllocPart(used.singles, planned.singles, 'singles'));
            parts.push(formatAllocPart(used.doubles, planned.doubles, 'doubles'));
            parts.push(formatAllocPart(used.triples, planned.triples, 'triples'));

            contentDiv.innerHTML = parts.join(', ');
            statusDiv.style.display = 'block';
        }

        function formatAllocPart(used, planned, label) {
            var cls = 'alloc-ok';
            if (used >= planned && planned > 0) cls = 'alloc-warn';
            if (used > planned) cls = 'alloc-over';
            return '<span class="' + cls + '">' + used + ' of ' + planned + ' ' + label + '</span>';
        }

        // =====================================================
        // Teacher Change -> Conflict Check + Cross-class Highlight
        // =====================================================
        function onTeacherChange() {
            var teacherId = document.getElementById('modalTeacherSelect').value;

            if (!teacherId) {
                document.getElementById('conflictIndicator').style.display = 'none';
                removeCrossClassHighlighting();
                updateSaveButtonState();
                return;
            }

            checkConflictsRealTime();

            var blockSize = parseInt(document.querySelector('input[name="block_type"]:checked').value);
            updateCrossClassHighlighting(parseInt(teacherId), currentDay, currentPeriod, blockSize);
        }

        // =====================================================
        // Block Type Change -> Re-check conflicts + break validation
        // =====================================================
        function onBlockTypeChange() {
            var blockSize = parseInt(document.querySelector('input[name="block_type"]:checked').value);
            hideBlockTypeWarning();

            // Check if block would span a break
            if (blockSize > 1) {
                if (blockSize === 2 && !isValidDoubleStart(currentPeriod)) {
                    showBlockTypeWarning(getDoubleAlignmentWarningText(currentPeriod), true);
                    document.getElementById('saveSlotBtn').disabled = true;
                    return;
                }

                var spansBreak = doesBlockSpanBreak(currentPeriod, blockSize);
                if (spansBreak) {
                    showBlockTypeWarning('This block would span across a break and is not allowed.', true);
                    document.getElementById('saveSlotBtn').disabled = true;
                    return;
                }
            } else if (!isValidDoubleStart(currentPeriod)) {
                showBlockTypeWarning(getDoubleAlignmentWarningText(currentPeriod), false);
            }

            if (document.getElementById('modalTeacherSelect').value) {
                checkConflictsRealTime();
                var teacherId = parseInt(document.getElementById('modalTeacherSelect').value);
                updateCrossClassHighlighting(teacherId, currentDay, currentPeriod, blockSize);
            }

            updateSaveButtonState();
        }

        /**
         * Check if a block starting at startPeriod with given size spans across a break
         * or exceeds available consecutive periods.
         */
        function doesBlockSpanBreak(startPeriod, blockSize) {
            if (blockSize <= 1) return false;

            var periodsCounted = 0;
            var started = false;

            for (var i = 0; i < daySchedule.length; i++) {
                var item = daySchedule[i];

                if (item.type === 'period' && parseInt(item.period) === startPeriod) {
                    started = true;
                }

                if (started) {
                    if (item.type === 'period') {
                        periodsCounted++;
                        if (periodsCounted >= blockSize) {
                            return false; // Enough consecutive periods found
                        }
                    } else if (item.type === 'break') {
                        return true; // Break found between periods of this block
                    }
                }
            }

            // Ran out of periods before reaching blockSize
            return true;
        }

        /**
         * Get the maximum block size allowed starting at a given period.
         * Checks both schedule structure (breaks) and occupied slots in the grid.
         */
        function getMaxBlockSize(startPeriod, klassId, day) {
            var maxSize = 0;
            var started = false;

            // Get current grid data to check occupied slots
            var klassGrid = currentGridData[klassId] || {};
            var dayData = klassGrid[day] || {};

            for (var i = 0; i < daySchedule.length; i++) {
                var item = daySchedule[i];

                if (item.type === 'period' && parseInt(item.period) === startPeriod) {
                    started = true;
                }

                if (started) {
                    if (item.type === 'period') {
                        var periodNum = parseInt(item.period);
                        // Adjacent periods must be empty (start period is always empty)
                        if (periodNum !== startPeriod && dayData[periodNum]) {
                            return maxSize; // Occupied slot blocks further extension
                        }
                        maxSize++;
                        if (maxSize >= 3) return 3; // Cap at triple
                    } else if (item.type === 'break') {
                        return maxSize;
                    }
                }
            }

            return maxSize;
        }

        // =====================================================
        // Real-time Conflict Check
        // =====================================================
        function checkConflictsRealTime() {
            var subjectSelect = document.getElementById('modalSubjectSelect');
            var subjectId = subjectSelect.value;
            var teacherId = document.getElementById('modalTeacherSelect').value;
            var blockSize = parseInt(document.querySelector('input[name="block_type"]:checked').value);
            var indicator = document.getElementById('conflictIndicator');

            if (!subjectId || !teacherId) {
                indicator.style.display = 'none';
                return;
            }

            // Build conflict check payload based on subject type
            var selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
            var subjectType = selectedOption.dataset.type || 'klass_subject';

            var payload = {
                timetable_id: timetableId,
                day_of_cycle: currentDay,
                period_number: currentPeriod,
                block_size: blockSize
            };

            if (subjectType === 'klass_subject') {
                payload.klass_subject_id = parseInt(subjectId);
            } else if (subjectType === 'optional_subject') {
                payload.optional_subject_id = parseInt(subjectId);
                payload.teacher_id = parseInt(teacherId);
                payload.klass_id = currentKlassId;
            } else {
                payload.teacher_id = parseInt(teacherId);
                payload.klass_id = currentKlassId;
            }

            // Show spinner
            indicator.innerHTML =
                '<div class="alert alert-info mb-0 py-2"><span class="spinner-border spinner-border-sm me-1"></span> Checking conflicts...</div>';
            indicator.style.display = 'block';

            fetch(conflictCheckUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        if (data.has_conflicts) {
                            var msgs = (data.conflicts || []).map(function(c) {
                                return escapeHtml(c);
                            }).join('<br>');
                            indicator.innerHTML = '<div class="alert alert-danger mb-0 py-2">' +
                                '<i class="mdi mdi-alert-circle me-1"></i>' + msgs + '</div>';
                            indicator.style.display = 'block';
                            document.getElementById('saveSlotBtn').disabled = true;
                        } else {
                            indicator.innerHTML = '<div class="alert alert-success mb-0 py-2">' +
                                '<i class="mdi mdi-check-circle me-1"></i> No conflicts detected.</div>';
                            indicator.style.display = 'block';
                            updateSaveButtonState();
                        }

                        // Show soft warnings if present (advisory only, does NOT block save)
                        var softIndicator = document.getElementById('softWarningIndicator');
                        if (data.warnings && data.warnings.length > 0) {
                            var warnMsgs = data.warnings.map(function(w) {
                                return escapeHtml(w.message);
                            }).join('<br>');
                            softIndicator.innerHTML = '<div class="alert alert-warning mb-0 py-2 mt-2">' +
                                '<i class="mdi mdi-alert me-1"></i><strong>Soft Warnings:</strong><br>' + warnMsgs + '</div>';
                            softIndicator.style.display = 'block';
                        } else {
                            softIndicator.style.display = 'none';
                        }
                    } else {
                        indicator.style.display = 'none';
                        document.getElementById('softWarningIndicator').style.display = 'none';
                    }
                })
                .catch(function(error) {
                    console.error('Error checking conflicts:', error);
                    indicator.style.display = 'none';
                    document.getElementById('softWarningIndicator').style.display = 'none';
                });
        }

        // =====================================================
        // Cross-Class Conflict Highlighting
        // =====================================================
        function updateCrossClassHighlighting(teacherId, day, periodStart, blockSize) {
            removeCrossClassHighlighting();
            if (!teacherId) return;

            var cells = document.querySelectorAll('.slot-filled');
            cells.forEach(function(cell) {
                var cellDay = parseInt(cell.dataset.day);
                var cellPeriod = parseInt(cell.dataset.period);
                var cellKlassId = parseInt(cell.dataset.klassId);
                var cellTeacherId = parseInt(cell.dataset.teacherId);

                if (cellDay !== day) return;
                if (cellKlassId === currentKlassId) return;
                if (cellTeacherId !== teacherId) return;

                // Check if cell's period overlaps with the block being assigned
                var cellBlockSize = parseInt(cell.dataset.blockSize) || 1;
                var cellEnd = cellPeriod + cellBlockSize - 1;
                var blockEnd = periodStart + blockSize - 1;

                if (cellPeriod <= blockEnd && cellEnd >= periodStart) {
                    cell.classList.add('slot-teacher-conflict');
                }
            });
        }

        function removeCrossClassHighlighting() {
            document.querySelectorAll('.slot-teacher-conflict').forEach(function(el) {
                el.classList.remove('slot-teacher-conflict');
            });
        }

        // =====================================================
        // Drag-and-Drop Handlers
        // =====================================================
        function handleDragStart(event) {
            var cell = event.target.closest('.slot-filled');
            if (!cell) return;

            if (cell.classList.contains('slot-locked')) {
                event.preventDefault();
                return;
            }

            isDragging = true;

            event.dataTransfer.setData('application/json', JSON.stringify({
                slotId: parseInt(cell.dataset.slotId),
                klassId: parseInt(cell.dataset.klassId),
                day: parseInt(cell.dataset.day),
                period: parseInt(cell.dataset.period),
                teacherId: cell.dataset.teacherId || ''
            }));
            event.dataTransfer.effectAllowed = 'move';
            cell.classList.add('dragging');
        }

        function handleDragOver(event) {
            var cell = event.target.closest('.slot-cell');
            if (!cell) return;
            if (cell.classList.contains('slot-break')) return;
            if (cell.classList.contains('dragging')) return;

            // Only allow drops on cells in the same class
            try {
                var draggingEl = document.querySelector('.slot-filled.dragging');
                if (!draggingEl) return;
                var sourceKlassId = draggingEl.dataset.klassId;
                var sourceBlockSize = parseInt(draggingEl.dataset.blockSize) || 1;
                if (cell.dataset.klassId !== sourceKlassId) return;

                // Multi-period blocks can only drop into empty cells.
                if (sourceBlockSize > 1 && !cell.classList.contains('slot-empty')) {
                    return;
                }
            } catch (e) {
                return;
            }

            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';

            if (cell.classList.contains('slot-empty')) {
                cell.classList.add('drag-over');
            } else if (cell.classList.contains('slot-filled') && !cell.classList.contains('slot-locked')) {
                cell.classList.add('drag-over-swap');
            }
        }

        function handleDragLeave(event) {
            var cell = event.target.closest('.slot-cell');
            if (cell) {
                cell.classList.remove('drag-over');
                cell.classList.remove('drag-over-swap');
            }
        }

        function handleDrop(event) {
            event.preventDefault();

            var targetCell = event.target.closest('.slot-cell');
            if (!targetCell) return;
            if (targetCell.classList.contains('slot-break')) return;

            targetCell.classList.remove('drag-over');
            targetCell.classList.remove('drag-over-swap');

            var sourceData;
            try {
                sourceData = JSON.parse(event.dataTransfer.getData('application/json'));
            } catch (e) {
                return;
            }

            if (parseInt(targetCell.dataset.klassId) !== sourceData.klassId) {
                displayMessage('Drag-and-drop is only supported within the same class.', 'error');
                return;
            }

            var targetDay = parseInt(targetCell.dataset.day);
            var targetPeriod = parseInt(targetCell.dataset.period);
            var isSwap = targetCell.classList.contains('slot-filled');
            var targetSlotId = isSwap ? parseInt(targetCell.dataset.slotId) : null;
            var draggingEl = document.querySelector('.slot-filled.dragging');
            var sourceBlockSize = draggingEl ? (parseInt(draggingEl.dataset.blockSize) || 1) : 1;

            if (isSwap && targetSlotId === sourceData.slotId) return;
            if (sourceBlockSize > 1 && isSwap) {
                displayMessage('Multi-period blocks can only be moved to empty cells.', 'error');
                return;
            }

            if (sourceBlockSize === 2 && !isValidDoubleStart(targetPeriod)) {
                displayMessage(getDoubleAlignmentWarningText(targetPeriod), 'error');
                return;
            }

            if (sourceBlockSize > 1 && doesBlockSpanBreak(targetPeriod, sourceBlockSize)) {
                displayMessage('This block would span across a break and cannot be moved there.', 'error');
                return;
            }

            // Pre-flight soft warning check before executing move/swap
            checkSoftWarningsThenExecute(sourceData.slotId, targetDay, targetPeriod, isSwap, targetSlotId);
        }

        function checkSoftWarningsThenExecute(slotId, targetDay, targetPeriod, isSwap, targetSlotId) {
            fetch(warningsUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    timetable_id: timetableId,
                    slot_id: slotId,
                    target_day: targetDay,
                    target_period: targetPeriod
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success && data.warnings && data.warnings.length > 0) {
                    var msgs = data.warnings.map(function(w) {
                        return '<li>' + escapeHtml(w.message) + '</li>';
                    }).join('');
                    Swal.fire({
                        title: 'Soft Constraint Warnings',
                        html: '<ul style="text-align:left; margin-bottom:0;">' + msgs + '</ul>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Proceed Anyway',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#f59e0b'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            if (isSwap) {
                                executeSwapSlots(slotId, targetSlotId);
                            } else {
                                executeMoveSlot(slotId, targetDay, targetPeriod);
                            }
                        }
                    });
                } else {
                    // No warnings -- proceed immediately (preserves snappy UX when no soft constraints)
                    if (isSwap) {
                        executeSwapSlots(slotId, targetSlotId);
                    } else {
                        executeMoveSlot(slotId, targetDay, targetPeriod);
                    }
                }
            })
            .catch(function() {
                // On network error, proceed without warning check (graceful degradation)
                if (isSwap) {
                    executeSwapSlots(slotId, targetSlotId);
                } else {
                    executeMoveSlot(slotId, targetDay, targetPeriod);
                }
            });
        }

        function handleDragEnd(event) {
            var cell = event.target.closest('.slot-filled');
            if (cell) cell.classList.remove('dragging');

            document.querySelectorAll('.drag-over, .drag-over-swap').forEach(function(el) {
                el.classList.remove('drag-over');
                el.classList.remove('drag-over-swap');
            });

            setTimeout(function() {
                isDragging = false;
            }, 100);
        }

        function executeMoveSlot(slotId, targetDay, targetPeriod) {
            fetch(moveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    slot_id: slotId,
                    target_day: targetDay,
                    target_period: targetPeriod
                })
            })
            .then(function(response) {
                return response.json().then(function(data) {
                    return { status: response.status, data: data };
                });
            })
            .then(function(result) {
                if (result.data.success) {
                    displayMessage(result.data.message || 'Slot moved successfully.');
                    allocationCache = {};
                    loadGridData();
                } else if (result.status === 409) {
                    var errors = result.data.errors || [];
                    var msg = errors.join(', ');
                    displayMessage(msg || 'Cannot move: conflicts detected.', 'error');
                } else {
                    displayMessage(result.data.message || 'Error moving slot.', 'error');
                }
            })
            .catch(function(error) {
                console.error('Error moving slot:', error);
                displayMessage('An error occurred while moving the slot.', 'error');
            });
        }

        function executeSwapSlots(slotIdA, slotIdB) {
            fetch(swapUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    slot_id_a: slotIdA,
                    slot_id_b: slotIdB
                })
            })
            .then(function(response) {
                return response.json().then(function(data) {
                    return { status: response.status, data: data };
                });
            })
            .then(function(result) {
                if (result.data.success) {
                    displayMessage(result.data.message || 'Slots swapped successfully.');
                    allocationCache = {};
                    loadGridData();
                } else if (result.status === 409) {
                    var errors = result.data.errors || [];
                    var msg = errors.join(', ');
                    displayMessage(msg || 'Cannot swap: conflicts detected.', 'error');
                } else {
                    displayMessage(result.data.message || 'Error swapping slots.', 'error');
                }
            })
            .catch(function(error) {
                console.error('Error swapping slots:', error);
                displayMessage('An error occurred while swapping slots.', 'error');
            });
        }

        // =====================================================
        // Save Slot Assignment
        // =====================================================
        function saveSlotAssignment() {
            var subjectSelect = document.getElementById('modalSubjectSelect');
            var subjectId = subjectSelect.value;
            var teacherId = document.getElementById('modalTeacherSelect').value;
            var blockSize = parseInt(document.querySelector('input[name="block_type"]:checked').value);

            if (!subjectId || !teacherId) {
                displayMessage('Please select both a subject and a teacher.', 'error');
                return;
            }

            // Build payload based on subject type
            var selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
            var subjectType = selectedOption.dataset.type || 'klass_subject';

            var payload = {
                timetable_id: timetableId,
                day_of_cycle: currentDay,
                period_number: currentPeriod,
                block_size: blockSize
            };

            if (subjectType === 'klass_subject') {
                payload.klass_subject_id = parseInt(subjectId);
            } else if (subjectType === 'optional_subject') {
                payload.optional_subject_id = parseInt(subjectId);
                payload.teacher_id = parseInt(teacherId);
                payload.klass_id = currentKlassId;
            } else {
                payload.grade_subject_id = parseInt(subjectId);
                payload.teacher_id = parseInt(teacherId);
                payload.klass_id = currentKlassId;
            }

            var saveBtn = document.getElementById('saveSlotBtn');
            saveBtn.classList.add('loading');
            saveBtn.disabled = true;

            fetch(assignUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                })
                .then(function(response) {
                    return response.json().then(function(data) {
                        return {
                            status: response.status,
                            data: data
                        };
                    });
                })
                .then(function(result) {
                    saveBtn.classList.remove('loading');
                    saveBtn.disabled = false;

                    if (result.status === 201 && result.data.success) {
                        assignmentModal.hide();
                        displayMessage(result.data.message || 'Slot assigned successfully.');
                        // Reset allocation cache so it re-fetches
                        allocationCache = {};
                        loadGridData();
                    } else if (result.status === 409) {
                        // Conflict from server
                        var errors = result.data.errors || [];
                        var msgs = errors.map(function(e) {
                            return escapeHtml(e);
                        }).join('<br>');
                        var indicator = document.getElementById('conflictIndicator');
                        indicator.innerHTML = '<div class="alert alert-danger mb-0 py-2">' +
                            '<i class="mdi mdi-alert-circle me-1"></i>' + msgs + '</div>';
                        indicator.style.display = 'block';
                        saveBtn.disabled = true;
                    } else if (result.status === 422) {
                        // Validation error
                        var msg = result.data.message || 'Validation error.';
                        displayMessage(msg, 'error');
                    } else {
                        displayMessage(result.data.message || 'Error assigning slot.', 'error');
                    }
                })
                .catch(function(error) {
                    saveBtn.classList.remove('loading');
                    saveBtn.disabled = false;
                    console.error('Error saving slot:', error);
                    displayMessage('An error occurred while assigning the slot.', 'error');
                });
        }

        // =====================================================
        // Delete Slot (SweetAlert2 confirmation)
        // =====================================================
        function confirmDeleteSlot(cell) {
            var slotId = cell.dataset.slotId;
            var blockSize = parseInt(cell.dataset.blockSize) || 1;

            if (!slotId) return;

            var subjectEl = cell.querySelector('.slot-subject');
            var teacherEl = cell.querySelector('.slot-teacher');
            var slotDesc = (subjectEl ? subjectEl.textContent : 'Unknown') +
                (teacherEl ? ' - ' + teacherEl.textContent : '');

            var confirmText = blockSize > 1 ?
                'This will delete the entire ' + getBlockLabel(blockSize).toLowerCase() + ' block (' + blockSize +
                ' periods).' :
                'This will remove this assignment.';

            Swal.fire({
                title: 'Delete this assignment?',
                html: '<strong>' + escapeHtml(slotDesc) + '</strong><br>' + confirmText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    deleteSlot(slotId);
                }
            });
        }

        function deleteSlot(slotId) {
            fetch(deleteUrlBase + '/' + slotId, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        displayMessage(data.message || 'Slot deleted successfully.');
                        allocationCache = {};
                        loadGridData();
                    } else {
                        displayMessage(data.message || 'Error deleting slot.', 'error');
                    }
                })
                .catch(function(error) {
                    console.error('Error deleting slot:', error);
                    displayMessage('An error occurred while deleting the slot.', 'error');
                });
        }

        // =====================================================
        // Save Button State Management
        // =====================================================
        function updateSaveButtonState() {
            var subjectId = document.getElementById('modalSubjectSelect').value;
            var teacherId = document.getElementById('modalTeacherSelect').value;
            var blockWarning = document.getElementById('blockTypeWarning');
            var indicator = document.getElementById('conflictIndicator');

            var hasBlockWarning = blockWarning &&
                blockWarning.style.display !== 'none' &&
                blockWarning.dataset.blocking === '1';
            var hasHardConflict = indicator && indicator.querySelector('.alert-danger') !== null;

            document.getElementById('saveSlotBtn').disabled = !subjectId || !teacherId || hasBlockWarning ||
                hasHardConflict;
        }

        // =====================================================
        // Message Display (matches period-settings pattern)
        // =====================================================
        function displayMessage(message, type) {
            type = type || 'success';
            var messageContainer = document.getElementById('messageContainer');
            var iconClass = type === 'success' ? 'mdi-check-all' : (type === 'error' ? 'mdi-block-helper' :
                'mdi-information');
            messageContainer.innerHTML =
                '<div class="row mb-3">' +
                '<div class="col-12">' +
                '<div class="alert alert-' + (type === 'error' ? 'danger' : type) +
                ' alert-dismissible alert-label-icon label-arrow fade show" role="alert">' +
                '<i class="mdi ' + iconClass + ' label-icon"></i>' +
                '<strong>' + message + '</strong>' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>' +
                '</div>' +
                '</div>';

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

            setTimeout(function() {
                var alert = messageContainer.querySelector('.alert');
                if (alert) {
                    var dismissBtn = alert.querySelector('.btn-close');
                    if (dismissBtn) dismissBtn.click();
                }
            }, 5000);
        }
    </script>
@endsection

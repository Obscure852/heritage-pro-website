@extends('layouts.master')
@section('title')
    Constraints - {{ $timetable->name }}
@endsection
@section('css')
    <style>
        .constraints-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .constraints-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .constraints-body {
            padding: 24px;
        }

        /* Scrollable Tabs */
        .nav-tabs-scroll {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            border-bottom: 2px solid #e5e7eb;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        .nav-tabs-scroll::-webkit-scrollbar {
            display: none;
        }

        .nav-tabs-scroll .nav-link {
            white-space: nowrap;
            border: none;
            border-bottom: 2px solid transparent;
            background: transparent;
            color: #6b7280;
            font-weight: 500;
            padding: 12px 18px;
            margin-bottom: -2px;
            transition: all 0.2s ease;
            border-radius: 0;
            font-size: 14px;
        }

        .nav-tabs-scroll .nav-link:hover {
            color: #4e73df;
            background: transparent;
        }

        .nav-tabs-scroll .nav-link.active {
            color: #4e73df;
            border-bottom-color: #4e73df;
            background: transparent;
        }

        .nav-tabs-scroll .nav-link i {
            color: #9ca3af;
        }

        .nav-tabs-scroll .nav-link.active i {
            color: #4e73df;
        }

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

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select,
        textarea.form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus,
        textarea.form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        /* Settings Form */
        .settings-section {
            margin-bottom: 32px;
        }

        .settings-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        /* Button Loading State */
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

        /* Teacher preferences table: match manage timetables look */
        .teacher-preferences-table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .teacher-preferences-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .teacher-preferences-table .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .teacher-preferences-table .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .teacher-preferences-table .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .teacher-preferences-table .action-buttons .btn i {
            font-size: 16px;
        }

        /* Constraint item cards */
        .constraint-item {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            margin-bottom: 12px;
            background: #fafbfc;
        }

        .constraint-item .constraint-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        /* Availability Grid */
        /* Availability grid styles moved to _teacher-availability-tab partial */

        /* Timetable selector */
        .timetable-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.2);
            padding: 6px 14px;
            border-radius: 3px;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .constraints-header {
                padding: 20px;
            }

            .constraints-body {
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
            Constraints
        @endslot
    @endcomponent

    <div id="messageContainer"></div>

    <div class="constraints-container">
        <div class="constraints-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h4 class="mb-1 text-white"><i class="bx bx-shield-quarter me-2"></i>Scheduling Constraints</h4>
                    <p class="mb-0 opacity-75">Configure teacher availability, room requirements, and workload rules for {{ $timetable->name }}</p>
                </div>
                <div class="timetable-badge">
                    <i class="fas fa-calendar-alt"></i>
                    {{ $timetable->name }}
                </div>
            </div>
        </div>
        <div class="constraints-body">
            <ul class="nav nav-tabs nav-tabs-scroll" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#teacherAvailability" role="tab">
                        <i class="fas fa-user-clock me-2"></i>Availability
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#teacherPreferences" role="tab">
                        <i class="fas fa-star me-2"></i>Preferences
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#roomRequirements" role="tab">
                        <i class="fas fa-door-open me-2"></i>Room Requirements
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#roomCapacity" role="tab">
                        <i class="fas fa-users me-2"></i>Room Capacity
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#subjectSpread" role="tab">
                        <i class="fas fa-calendar-alt me-2"></i>Subject Spread
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#consecutiveLimits" role="tab">
                        <i class="fas fa-hourglass-half me-2"></i>Consecutive Limits
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#electiveCoupling" role="tab">
                        <i class="fas fa-link me-2"></i>Elective Coupling
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#subjectPairs" role="tab">
                        <i class="fas fa-exchange-alt me-2"></i>Subject Pairs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#periodRestrictions" role="tab">
                        <i class="fas fa-clock me-2"></i>Period Restrictions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#teacherRoomAssignment" role="tab">
                        <i class="fas fa-home me-2"></i>Home Rooms
                    </a>
                </li>
            </ul>

            <div class="tab-content pt-3">
                <div class="tab-pane active" id="teacherAvailability" role="tabpanel">
                    @include('timetable.constraints._teacher-availability-tab')
                </div>
                <div class="tab-pane" id="teacherPreferences" role="tabpanel">
                    @include('timetable.constraints._teacher-preferences-tab')
                </div>
                <div class="tab-pane" id="roomRequirements" role="tabpanel">
                    @include('timetable.constraints._room-requirements-tab')
                </div>
                <div class="tab-pane" id="roomCapacity" role="tabpanel">
                    @include('timetable.constraints._room-capacity-tab')
                </div>
                <div class="tab-pane" id="subjectSpread" role="tabpanel">
                    @include('timetable.constraints._subject-spread-tab')
                </div>
                <div class="tab-pane" id="consecutiveLimits" role="tabpanel">
                    @include('timetable.constraints._consecutive-limits-tab')
                </div>
                <div class="tab-pane" id="electiveCoupling" role="tabpanel">
                    @include('timetable.constraints._elective-coupling-tab')
                </div>
                <div class="tab-pane" id="subjectPairs" role="tabpanel">
                    @include('timetable.constraints._subject-pair-tab')
                </div>
                <div class="tab-pane" id="periodRestrictions" role="tabpanel">
                    @include('timetable.constraints._period-restriction-tab')
                </div>
                <div class="tab-pane" id="teacherRoomAssignment" role="tabpanel">
                    @include('timetable.constraints._teacher-room-assignment-tab')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // ========================================
        // Shared Variables
        // ========================================
        var csrfToken = '{{ csrf_token() }}';
        var teachers = @json($teachers);
        var subjects = @json($subjects);
        var venues = @json($venues);
        var venueTypes = @json($venueTypes);
        var periodsPerDay = parseInt(@json($periodsPerDay));
        var timetableId = @json($timetable->id);
        var klasses = @json($klasses);
        var periodDefinitions = @json($periodDefinitions);
        var constraints = @json($constraints);

        document.addEventListener('DOMContentLoaded', function() {
            // Tab persistence
            var tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
            tabLinks.forEach(function(tabLink) {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    var activeTabHref = event.target.getAttribute('href');
                    localStorage.setItem('constraintsActiveTab', activeTabHref);

                    // Scroll active tab into view
                    event.target.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                });
            });

            // Check for hash in URL first
            var hash = window.location.hash;
            if (hash) {
                var tabTriggerEl = document.querySelector('.nav-link[href="' + hash + '"]');
                if (tabTriggerEl) {
                    var tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                    history.replaceState(null, null, window.location.pathname);
                }
            } else {
                // Fall back to localStorage
                var activeTab = localStorage.getItem('constraintsActiveTab');
                if (activeTab) {
                    var tabTriggerEl = document.querySelector('.nav-link[href="' + activeTab + '"]');
                    if (tabTriggerEl) {
                        var tab = new bootstrap.Tab(tabTriggerEl);
                        tab.show();
                    }
                }
            }

            // Initialize tab functionalities
            initializeAvailabilityTab();
            initializePreferencesTab();
            initializeRoomRequirementsTab();
            initializeRoomCapacityTab();
            initializeSubjectSpreadTab();
            initializeConsecutiveLimitsTab();
            initializeSubjectPairTab();
            initializePeriodRestrictionTab();
            initializeTeacherRoomAssignmentTab();
        });

        // ========================================
        // Message Display
        // ========================================
        function displayMessage(message, type) {
            type = type || 'success';
            var messageContainer = document.getElementById('messageContainer');
            var iconClass = type === 'success' ? 'mdi-check-all' : (type === 'error' ? 'mdi-block-helper' : 'mdi-information');
            messageContainer.innerHTML =
                '<div class="row mb-3">' +
                    '<div class="col-12">' +
                        '<div class="alert alert-' + (type === 'error' ? 'danger' : type) + ' alert-dismissible alert-label-icon label-arrow fade show" role="alert">' +
                            '<i class="mdi ' + iconClass + ' label-icon"></i>' +
                            '<strong>' + message + '</strong>' +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>' +
                    '</div>' +
                '</div>';

            window.scrollTo({ top: 0, behavior: 'smooth' });

            setTimeout(function() {
                var alert = messageContainer.querySelector('.alert');
                if (alert) {
                    var dismissBtn = alert.querySelector('.btn-close');
                    if (dismissBtn) dismissBtn.click();
                }
            }, 5000);
        }

        // ========================================
        // Teacher Availability Tab (CONST-01)
        // ========================================
        function initializeAvailabilityTab() {
            var teacherSelect = document.getElementById('availabilityTeacherSelect');
            var gridContainer = document.getElementById('availabilityGridContainer');
            var saveBtn = document.getElementById('saveAvailabilityBtn');

            if (!teacherSelect) return;

            teacherSelect.addEventListener('change', function() {
                var teacherId = this.value;
                if (!teacherId) {
                    gridContainer.style.display = 'none';
                    saveBtn.style.display = 'none';
                    return;
                }
                loadTeacherAvailability(teacherId);
            });

            // Click handler for grid cells (delegate on grid container)
            gridContainer.addEventListener('click', function(e) {
                var cell = e.target.closest('.availability-cell');
                if (!cell) return;
                toggleAvailabilityCell(cell);
            });

            saveBtn.addEventListener('click', function() {
                saveTeacherAvailability();
            });

            // Auto-select first teacher
            if (teacherSelect.options.length > 1) {
                teacherSelect.selectedIndex = 1;
                loadTeacherAvailability(teacherSelect.value);
            }
        }

        function loadTeacherAvailability(teacherId) {
            // Find existing constraint for this teacher from server-rendered data
            var availConstraints = constraints['teacher_availability'] || [];
            var existing = null;
            for (var i = 0; i < availConstraints.length; i++) {
                var c = availConstraints[i];
                if (c.constraint_config && c.constraint_config.teacher_id == teacherId) {
                    existing = c;
                    break;
                }
            }

            var unavailableSlots = existing ? (existing.constraint_config.unavailable_slots || []) : [];

            buildAvailabilityGrid(unavailableSlots);
            document.getElementById('availabilityGridContainer').style.display = 'block';
            document.getElementById('saveAvailabilityBtn').style.display = '';
        }

        function buildAvailabilityGrid(unavailableSlots) {
            var gridBody = document.getElementById('availabilityGridBody');
            var html = '';

            // Build a lookup set for unavailable slots
            var unavailableSet = {};
            unavailableSlots.forEach(function(slot) {
                var key = parseInt(slot.day_of_cycle) + '-' + parseInt(slot.period_number);
                unavailableSet[key] = true;
            });

            for (var p = 1; p <= periodsPerDay; p++) {
                html += '<tr>';
                html += '<th>Period ' + p + '</th>';
                for (var d = 1; d <= 6; d++) {
                    var key = d + '-' + p;
                    var isUnavailable = unavailableSet[key] === true;
                    var cellClass = isUnavailable ? 'unavailable' : 'available';
                    var icon = isUnavailable ? '<i class="fas fa-times"></i>' : '<i class="fas fa-check"></i>';
                    html += '<td><div class="availability-cell ' + cellClass + '" data-day="' + d + '" data-period="' + p + '">' + icon + '</div></td>';
                }
                html += '</tr>';
            }

            gridBody.innerHTML = html;
            updateAvailabilityStats();
        }

        function toggleAvailabilityCell(cell) {
            if (cell.classList.contains('available')) {
                cell.classList.remove('available');
                cell.classList.add('unavailable');
                cell.innerHTML = '<i class="fas fa-times"></i>';
            } else {
                cell.classList.remove('unavailable');
                cell.classList.add('available');
                cell.innerHTML = '<i class="fas fa-check"></i>';
            }
            updateAvailabilityStats();
        }

        function updateAvailabilityStats() {
            var available = document.querySelectorAll('.availability-cell.available').length;
            var unavailable = document.querySelectorAll('.availability-cell.unavailable').length;
            var total = available + unavailable;
            var availEl = document.getElementById('availableCount');
            var unavailEl = document.getElementById('unavailableCount');
            var totalEl = document.getElementById('totalSlots');
            if (availEl) availEl.textContent = available;
            if (unavailEl) unavailEl.textContent = unavailable;
            if (totalEl) totalEl.textContent = total;
        }

        function saveTeacherAvailability() {
            var teacherId = document.getElementById('availabilityTeacherSelect').value;
            if (!teacherId) return;

            // Collect all unavailable cells
            var unavailableSlots = [];
            document.querySelectorAll('.availability-cell.unavailable').forEach(function(cell) {
                unavailableSlots.push({
                    day_of_cycle: parseInt(cell.dataset.day),
                    period_number: parseInt(cell.dataset.period)
                });
            });

            var btn = document.getElementById('saveAvailabilityBtn');
            btn.classList.add('loading');
            btn.disabled = true;

            fetch('{{ route("timetable.constraints.save-teacher-availability") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    timetable_id: timetableId,
                    teacher_id: parseInt(teacherId),
                    unavailable_slots: unavailableSlots
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.classList.remove('loading');
                btn.disabled = false;
                displayMessage(data.message || (data.success ? 'Saved.' : 'Error.'), data.success ? 'success' : 'error');

                // Update local constraints cache
                if (data.success) {
                    updateLocalConstraint('teacher_availability', 'teacher_id', parseInt(teacherId), {
                        teacher_id: parseInt(teacherId),
                        unavailable_slots: unavailableSlots
                    });
                }
            })
            .catch(function() {
                btn.classList.remove('loading');
                btn.disabled = false;
                displayMessage('An error occurred while saving.', 'error');
            });
        }

        // ========================================
        // Teacher Preferences Tab (CONST-02)
        // ========================================
        function initializePreferencesTab() {
            var saveBtns = document.querySelectorAll('.save-preference-btn');
            saveBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var teacherId = this.dataset.teacherId;
                    var select = document.getElementById('preference_' + teacherId);
                    saveTeacherPreference(teacherId, select.value, this);
                });
            });
        }

        function saveTeacherPreference(teacherId, preference, btn) {
            btn.classList.add('loading');
            btn.disabled = true;

            fetch('{{ route("timetable.constraints.save-teacher-preference") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    timetable_id: timetableId,
                    teacher_id: parseInt(teacherId),
                    preference: preference
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.classList.remove('loading');
                btn.disabled = false;
                displayMessage(data.message || (data.success ? 'Saved.' : 'Error.'), data.success ? 'success' : 'error');
            })
            .catch(function() {
                btn.classList.remove('loading');
                btn.disabled = false;
                displayMessage('An error occurred while saving.', 'error');
            });
        }

        // ========================================
        // Shared Helper: Update local constraints cache
        // ========================================
        function updateLocalConstraint(type, keyField, keyValue, newConfig) {
            if (!constraints[type]) {
                constraints[type] = [];
            }
            var found = false;
            for (var i = 0; i < constraints[type].length; i++) {
                if (constraints[type][i].constraint_config && constraints[type][i].constraint_config[keyField] == keyValue) {
                    constraints[type][i].constraint_config = newConfig;
                    found = true;
                    break;
                }
            }
            if (!found) {
                constraints[type].push({
                    constraint_config: newConfig,
                    is_active: true
                });
            }
        }

        // ========================================
        // Shared Helper: Delete constraint via AJAX
        // ========================================
        function deleteConstraint(constraintId, onSuccess) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete this constraint?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete it'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        performDelete(constraintId, onSuccess);
                    }
                });
            } else {
                if (confirm('Delete this constraint? This action cannot be undone.')) {
                    performDelete(constraintId, onSuccess);
                }
            }
        }

        function performDelete(constraintId, onSuccess) {
            fetch('/timetable/constraints/delete/' + constraintId, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    displayMessage(data.message || 'Constraint deleted.');
                    if (onSuccess) onSuccess();
                } else {
                    displayMessage(data.message || 'Error deleting constraint.', 'error');
                }
            })
            .catch(function() {
                displayMessage('An error occurred while deleting.', 'error');
            });
        }

        // ========================================
        // Room Requirements Tab (CONST-03)
        // ========================================
        function initializeRoomRequirementsTab() {
            // Build subject dropdown excluding already-configured subjects
            populateRoomReqSubjectDropdown();

            var addBtn = document.getElementById('addRoomRequirementBtn');
            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    addRoomRequirement();
                });
            }

            // Delegate save/delete buttons
            var container = document.getElementById('roomRequirementsContainer');
            if (container) {
                container.addEventListener('click', function(e) {
                    var saveBtn = e.target.closest('.save-room-req-btn');
                    if (saveBtn) {
                        var row = saveBtn.closest('tr');
                        var subjectId = saveBtn.dataset.subjectId;
                        var venueType = row.querySelector('.room-req-venue-type').value;
                        saveRoomRequirement(subjectId, venueType, saveBtn);
                        return;
                    }

                    var deleteBtn = e.target.closest('.delete-room-req-btn');
                    if (deleteBtn) {
                        var constraintId = deleteBtn.dataset.constraintId;
                        var row = deleteBtn.closest('tr');
                        deleteConstraint(constraintId, function() {
                            row.remove();
                            renumberTableRows('roomRequirementsBody');
                            populateRoomReqSubjectDropdown();
                        });
                    }
                });
            }
        }

        function populateRoomReqSubjectDropdown() {
            var select = document.getElementById('roomReqSubjectSelect');
            if (!select) return;

            // Get already-configured subject IDs
            var configuredIds = [];
            var rows = document.querySelectorAll('#roomRequirementsBody tr');
            rows.forEach(function(row) {
                var sid = row.dataset.subjectId;
                if (sid) configuredIds.push(parseInt(sid));
            });

            select.innerHTML = '<option value="">-- Select Subject --</option>';
            subjects.forEach(function(s) {
                if (configuredIds.indexOf(s.id) === -1) {
                    select.innerHTML += '<option value="' + s.id + '">' + s.name + '</option>';
                }
            });
        }

        function addRoomRequirement() {
            var subjectSelect = document.getElementById('roomReqSubjectSelect');
            var venueTypeSelect = document.getElementById('roomReqVenueTypeSelect');
            var subjectId = subjectSelect.value;
            var venueType = venueTypeSelect.value;

            if (!subjectId) {
                displayMessage('Please select a subject.', 'error');
                return;
            }
            if (!venueType) {
                displayMessage('Please select a venue type.', 'error');
                return;
            }

            saveRoomRequirement(subjectId, venueType, document.getElementById('addRoomRequirementBtn'), true);
        }

        function saveRoomRequirement(subjectId, venueType, btn, isNew) {
            btn.disabled = true;
            if (btn.classList.contains('btn-loading')) btn.classList.add('loading');

            fetch('{{ route("timetable.constraints.save-room-requirement") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    timetable_id: timetableId,
                    subject_id: parseInt(subjectId),
                    required_venue_type: venueType
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;
                if (btn.classList.contains('btn-loading')) btn.classList.remove('loading');
                displayMessage(data.message || (data.success ? 'Saved.' : 'Error.'), data.success ? 'success' : 'error');

                if (data.success && isNew) {
                    // Show table, hide empty state
                    var emptyState = document.getElementById('roomReqEmptyState');
                    if (emptyState) emptyState.style.display = 'none';
                    var tableWrapper = document.querySelector('#roomRequirementsContainer .table-responsive');
                    if (tableWrapper) tableWrapper.style.display = '';

                    // Add new row to table
                    var tbody = document.getElementById('roomRequirementsBody');
                    var subjectName = '';
                    subjects.forEach(function(s) { if (s.id == subjectId) subjectName = s.name; });
                    var rowCount = tbody.querySelectorAll('tr').length + 1;

                    var venueOptionsHtml = '';
                    venueTypes.forEach(function(vt) {
                        venueOptionsHtml += '<option value="' + vt + '"' + (vt === venueType ? ' selected' : '') + '>' + vt + '</option>';
                    });

                    // Badge styling for venue type
                    var vtIcons = {
                        'Classroom': 'fas fa-chalkboard', 'Laboratory': 'fas fa-flask', 'Lab': 'fas fa-flask',
                        'Computer Lab': 'fas fa-desktop', 'Workshop': 'fas fa-tools', 'Hall': 'fas fa-archway',
                        'Library': 'fas fa-book', 'Sports': 'fas fa-running', 'Field': 'fas fa-running'
                    };
                    var vtIcon = vtIcons[venueType] || 'fas fa-door-open';
                    var vtClass = 'type-' + venueType.toLowerCase().replace(/\s+/g, '-');

                    var newRow = document.createElement('tr');
                    newRow.dataset.constraintId = data.constraint_id || '';
                    newRow.dataset.subjectId = subjectId;
                    newRow.innerHTML =
                        '<td><span class="row-num">' + rowCount + '</span></td>' +
                        '<td><span class="subject-name">' + subjectName + '</span></td>' +
                        '<td><span class="venue-type-badge ' + vtClass + '"><i class="' + vtIcon + '"></i> ' + venueType + '</span></td>' +
                        '<td><select class="room-req-venue-select room-req-venue-type">' + venueOptionsHtml + '</select></td>' +
                        '<td>' +
                            '<div class="action-btns justify-content-center">' +
                                '<button type="button" class="btn btn-sm btn-primary btn-loading save-room-req-btn" data-subject-id="' + subjectId + '">' +
                                    '<span class="btn-text"><i class="fas fa-save"></i></span>' +
                                    '<span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span></span>' +
                                '</button>' +
                                '<button type="button" class="btn btn-sm btn-outline-danger delete-room-req-btn" data-constraint-id="' + (data.constraint_id || '') + '">' +
                                    '<i class="fas fa-trash-alt"></i>' +
                                '</button>' +
                            '</div>' +
                        '</td>';
                    tbody.appendChild(newRow);

                    // Reset add form
                    document.getElementById('roomReqSubjectSelect').value = '';
                    document.getElementById('roomReqVenueTypeSelect').value = '';
                    populateRoomReqSubjectDropdown();
                }
            })
            .catch(function() {
                btn.disabled = false;
                if (btn.classList.contains('btn-loading')) btn.classList.remove('loading');
                displayMessage('An error occurred while saving.', 'error');
            });
        }

        // ========================================
        // Room Capacity Tab (CONST-04)
        // ========================================
        function initializeRoomCapacityTab() {
            var saveBtn = document.getElementById('saveRoomCapacityBtn');
            if (!saveBtn) return;

            saveBtn.addEventListener('click', function() {
                saveRoomCapacity();
            });
        }

        function saveRoomCapacity() {
            var enabled = document.getElementById('roomCapacityEnabled').checked;
            var enforcement = document.querySelector('input[name="enforcement"]:checked');
            var enforcementValue = enforcement ? enforcement.value : 'strict';

            var btn = document.getElementById('saveRoomCapacityBtn');
            btn.classList.add('loading');
            btn.disabled = true;

            fetch('{{ route("timetable.constraints.save-room-capacity") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    timetable_id: timetableId,
                    enabled: enabled,
                    enforcement: enforcementValue
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.classList.remove('loading');
                btn.disabled = false;
                displayMessage(data.message || (data.success ? 'Saved.' : 'Error.'), data.success ? 'success' : 'error');
            })
            .catch(function() {
                btn.classList.remove('loading');
                btn.disabled = false;
                displayMessage('An error occurred while saving.', 'error');
            });
        }

        // ========================================
        // Subject Spread Tab (CONST-05)
        // ========================================
        function initializeSubjectSpreadTab() {
            populateSpreadSubjectDropdown();

            var addBtn = document.getElementById('addSubjectSpreadBtn');
            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    addSubjectSpread();
                });
            }

            // Delegate save/delete buttons
            var container = document.getElementById('subjectSpreadContainer');
            if (container) {
                container.addEventListener('click', function(e) {
                    var saveBtn = e.target.closest('.save-spread-btn');
                    if (saveBtn) {
                        var row = saveBtn.closest('tr');
                        var subjectId = saveBtn.dataset.subjectId;
                        var maxPerDay = parseInt(row.querySelector('.spread-max-per-day').value) || 1;
                        var distribute = row.querySelector('.spread-distribute').checked;
                        saveSubjectSpread(subjectId, maxPerDay, distribute, saveBtn);
                        return;
                    }

                    var deleteBtn = e.target.closest('.delete-spread-btn');
                    if (deleteBtn) {
                        var constraintId = deleteBtn.dataset.constraintId;
                        var row = deleteBtn.closest('tr');
                        deleteConstraint(constraintId, function() {
                            row.remove();
                            renumberTableRows('subjectSpreadBody');
                            populateSpreadSubjectDropdown();
                        });
                    }
                });
            }
        }

        function populateSpreadSubjectDropdown() {
            var select = document.getElementById('spreadSubjectSelect');
            if (!select) return;

            var configuredIds = [];
            var rows = document.querySelectorAll('#subjectSpreadBody tr');
            rows.forEach(function(row) {
                var sid = row.dataset.subjectId;
                if (sid) configuredIds.push(parseInt(sid));
            });

            select.innerHTML = '<option value="">-- Select Subject --</option>';
            subjects.forEach(function(s) {
                if (configuredIds.indexOf(s.id) === -1) {
                    select.innerHTML += '<option value="' + s.id + '">' + s.name + '</option>';
                }
            });
        }

        function addSubjectSpread() {
            var subjectSelect = document.getElementById('spreadSubjectSelect');
            var maxPerDayInput = document.getElementById('spreadMaxPerDay');
            var distributeCheck = document.getElementById('spreadDistribute');
            var subjectId = subjectSelect.value;

            if (!subjectId) {
                displayMessage('Please select a subject.', 'error');
                return;
            }

            var maxPerDay = parseInt(maxPerDayInput.value) || 1;
            var distribute = distributeCheck.checked;

            saveSubjectSpread(subjectId, maxPerDay, distribute, document.getElementById('addSubjectSpreadBtn'), true);
        }

        function saveSubjectSpread(subjectId, maxPerDay, distribute, btn, isNew) {
            btn.disabled = true;
            if (btn.classList.contains('btn-loading')) btn.classList.add('loading');

            fetch('{{ route("timetable.constraints.save-subject-spread") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    timetable_id: timetableId,
                    subject_id: parseInt(subjectId),
                    max_lessons_per_day: maxPerDay,
                    distribute_across_cycle: distribute
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;
                if (btn.classList.contains('btn-loading')) btn.classList.remove('loading');
                displayMessage(data.message || (data.success ? 'Saved.' : 'Error.'), data.success ? 'success' : 'error');

                if (data.success && isNew) {
                    var emptyState = document.getElementById('spreadEmptyState');
                    if (emptyState) emptyState.style.display = 'none';
                    var tableWrapper = document.querySelector('#subjectSpreadContainer .table-responsive');
                    if (tableWrapper) tableWrapper.style.display = '';

                    var tbody = document.getElementById('subjectSpreadBody');
                    var subjectName = '';
                    subjects.forEach(function(s) { if (s.id == subjectId) subjectName = s.name; });
                    var rowCount = tbody.querySelectorAll('tr').length + 1;

                    var newRow = document.createElement('tr');
                    newRow.dataset.constraintId = data.constraint_id || '';
                    newRow.dataset.subjectId = subjectId;
                    newRow.innerHTML =
                        '<td><span class="row-num">' + rowCount + '</span></td>' +
                        '<td><span class="subject-name">' + subjectName + '</span></td>' +
                        '<td class="text-center"><input type="number" class="spread-input spread-max-per-day" value="' + maxPerDay + '" min="1" max="' + periodsPerDay + '"></td>' +
                        '<td class="text-center"><input type="checkbox" class="form-check-input spread-distribute"' + (distribute ? ' checked' : '') + '></td>' +
                        '<td>' +
                            '<div class="action-btns justify-content-center">' +
                                '<button type="button" class="btn btn-sm btn-primary btn-loading save-spread-btn" data-subject-id="' + subjectId + '">' +
                                    '<span class="btn-text"><i class="fas fa-save"></i></span>' +
                                    '<span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span></span>' +
                                '</button>' +
                                '<button type="button" class="btn btn-sm btn-outline-danger delete-spread-btn" data-constraint-id="' + (data.constraint_id || '') + '">' +
                                    '<i class="fas fa-trash-alt"></i>' +
                                '</button>' +
                            '</div>' +
                        '</td>';
                    tbody.appendChild(newRow);

                    // Reset
                    document.getElementById('spreadSubjectSelect').value = '';
                    document.getElementById('spreadMaxPerDay').value = '1';
                    document.getElementById('spreadDistribute').checked = true;
                    populateSpreadSubjectDropdown();
                }
            })
            .catch(function() {
                btn.disabled = false;
                if (btn.classList.contains('btn-loading')) btn.classList.remove('loading');
                displayMessage('An error occurred while saving.', 'error');
            });
        }

        // ========================================
        // Consecutive Limits Tab (CONST-06)
        // ========================================
        function initializeConsecutiveLimitsTab() {
            populateConsecutiveTeacherDropdown();

            // Global save
            var globalBtn = document.getElementById('saveGlobalConsecutiveBtn');
            if (globalBtn) {
                globalBtn.addEventListener('click', function() {
                    saveGlobalConsecutiveLimit();
                });
            }

            // Add override
            var addBtn = document.getElementById('addConsecutiveOverrideBtn');
            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    addConsecutiveOverride();
                });
            }

            // Delegate save/delete
            var container = document.getElementById('consecutiveOverridesContainer');
            if (container) {
                container.addEventListener('click', function(e) {
                    var saveBtn = e.target.closest('.save-consecutive-btn');
                    if (saveBtn) {
                        var row = saveBtn.closest('tr');
                        var teacherId = saveBtn.dataset.teacherId;
                        var maxConsec = parseInt(row.querySelector('.consecutive-max-input').value) || 3;
                        saveConsecutiveLimit(teacherId, maxConsec, saveBtn);
                        return;
                    }

                    var deleteBtn = e.target.closest('.delete-consecutive-btn');
                    if (deleteBtn) {
                        var constraintId = deleteBtn.dataset.constraintId;
                        var row = deleteBtn.closest('tr');
                        deleteConstraint(constraintId, function() {
                            row.remove();
                            renumberTableRows('consecutiveOverridesBody');
                            populateConsecutiveTeacherDropdown();
                        });
                    }
                });
            }
        }

        function populateConsecutiveTeacherDropdown() {
            var select = document.getElementById('consecutiveTeacherSelect');
            if (!select) return;

            var configuredIds = [];
            var rows = document.querySelectorAll('#consecutiveOverridesBody tr');
            rows.forEach(function(row) {
                var tid = row.dataset.teacherId;
                if (tid) configuredIds.push(parseInt(tid));
            });

            select.innerHTML = '<option value="">-- Select Teacher --</option>';
            teachers.forEach(function(t) {
                if (configuredIds.indexOf(t.id) === -1) {
                    select.innerHTML += '<option value="' + t.id + '">' + t.firstname + ' ' + t.lastname + '</option>';
                }
            });
        }

        function saveGlobalConsecutiveLimit() {
            var maxConsec = parseInt(document.getElementById('globalConsecutiveLimit').value) || 3;
            var btn = document.getElementById('saveGlobalConsecutiveBtn');
            btn.classList.add('loading');
            btn.disabled = true;

            fetch('{{ route("timetable.constraints.save-consecutive-limit") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    timetable_id: timetableId,
                    teacher_id: null,
                    max_consecutive_periods: maxConsec
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.classList.remove('loading');
                btn.disabled = false;
                displayMessage(data.message || (data.success ? 'Saved.' : 'Error.'), data.success ? 'success' : 'error');
            })
            .catch(function() {
                btn.classList.remove('loading');
                btn.disabled = false;
                displayMessage('An error occurred while saving.', 'error');
            });
        }

        function addConsecutiveOverride() {
            var teacherSelect = document.getElementById('consecutiveTeacherSelect');
            var maxInput = document.getElementById('consecutiveTeacherMax');
            var teacherId = teacherSelect.value;

            if (!teacherId) {
                displayMessage('Please select a teacher.', 'error');
                return;
            }

            var maxConsec = parseInt(maxInput.value) || 3;
            saveConsecutiveLimit(teacherId, maxConsec, document.getElementById('addConsecutiveOverrideBtn'), true);
        }

        function saveConsecutiveLimit(teacherId, maxConsec, btn, isNew) {
            btn.disabled = true;
            if (btn.classList.contains('btn-loading')) btn.classList.add('loading');

            fetch('{{ route("timetable.constraints.save-consecutive-limit") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    timetable_id: timetableId,
                    teacher_id: parseInt(teacherId),
                    max_consecutive_periods: maxConsec
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;
                if (btn.classList.contains('btn-loading')) btn.classList.remove('loading');
                displayMessage(data.message || (data.success ? 'Saved.' : 'Error.'), data.success ? 'success' : 'error');

                if (data.success && isNew) {
                    var tbody = document.getElementById('consecutiveOverridesBody');

                    // Prevent duplicate rows for the same teacher
                    var existingRow = tbody.querySelector('tr[data-teacher-id="' + teacherId + '"]');
                    if (existingRow) {
                        existingRow.querySelector('.consecutive-max-input').value = maxConsec;
                        if (data.constraint_id) existingRow.dataset.constraintId = data.constraint_id;
                    } else {
                        var emptyState = document.getElementById('consecutiveEmptyState');
                        if (emptyState) emptyState.style.display = 'none';
                        var tableWrapper = document.querySelector('#consecutiveOverridesContainer .table-responsive');
                        if (tableWrapper) tableWrapper.style.display = '';

                        var teacherName = '';
                        teachers.forEach(function(t) { if (t.id == teacherId) teacherName = t.firstname + ' ' + t.lastname; });
                        var rowCount = tbody.querySelectorAll('tr').length + 1;

                        var newRow = document.createElement('tr');
                        newRow.dataset.constraintId = data.constraint_id || '';
                        newRow.dataset.teacherId = teacherId;
                        newRow.innerHTML =
                            '<td><span class="row-num">' + rowCount + '</span></td>' +
                            '<td><span class="teacher-name">' + teacherName + '</span></td>' +
                            '<td class="text-center"><input type="number" class="consec-input consecutive-max-input" value="' + maxConsec + '" min="1" max="10"></td>' +
                            '<td>' +
                                '<div class="action-btns justify-content-center">' +
                                    '<button type="button" class="btn btn-sm btn-primary btn-loading save-consecutive-btn" data-teacher-id="' + teacherId + '">' +
                                        '<span class="btn-text"><i class="fas fa-save"></i></span>' +
                                        '<span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span></span>' +
                                    '</button>' +
                                    '<button type="button" class="btn btn-sm btn-outline-danger delete-consecutive-btn" data-constraint-id="' + (data.constraint_id || '') + '">' +
                                        '<i class="fas fa-trash-alt"></i>' +
                                    '</button>' +
                                '</div>' +
                            '</td>';
                        tbody.appendChild(newRow);
                    }

                    // Reset
                    document.getElementById('consecutiveTeacherSelect').value = '';
                    document.getElementById('consecutiveTeacherMax').value = '3';
                    populateConsecutiveTeacherDropdown();
                }
            })
            .catch(function() {
                btn.disabled = false;
                if (btn.classList.contains('btn-loading')) btn.classList.remove('loading');
                displayMessage('An error occurred while saving.', 'error');
            });
        }

        // ========================================
        // Shared Helper: Renumber table rows
        // ========================================
        function renumberTableRows(tbodyId) {
            var rows = document.querySelectorAll('#' + tbodyId + ' tr');
            rows.forEach(function(row, i) {
                var rowNum = row.querySelector('td:first-child .row-num');
                if (rowNum) {
                    rowNum.textContent = (i + 1);
                } else {
                    var firstTd = row.querySelector('td:first-child');
                    if (firstTd) firstTd.textContent = (i + 1);
                }
            });
        }

        // ========================================
        // Subject Pair Tab (CONST-08)
        // ========================================
        var ruleLabels = {
            'not_same_day': 'Must not be on same day',
            'not_consecutive': 'Must not be back-to-back',
            'must_same_day': 'Must be on same day',
            'must_follow': 'Must be adjacent'
        };
        var ruleIcons = {
            'not_same_day': 'fas fa-ban',
            'not_consecutive': 'fas fa-arrows-alt-h',
            'must_same_day': 'fas fa-calendar-day',
            'must_follow': 'fas fa-link'
        };

        function initializeSubjectPairTab() {
            populatePairDropdowns();

            var addBtn = document.getElementById('addSubjectPairBtn');
            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    addSubjectPair();
                });
            }

            var container = document.getElementById('subjectPairContainer');
            if (container) {
                container.addEventListener('click', function(e) {
                    var deleteBtn = e.target.closest('.delete-pair-btn');
                    if (deleteBtn) {
                        var constraintId = deleteBtn.dataset.constraintId;
                        var row = deleteBtn.closest('tr');
                        deleteConstraint(constraintId, function() {
                            row.remove();
                            renumberTableRows('subjectPairBody');
                        });
                    }
                });
            }
        }

        function populatePairDropdowns() {
            var selectA = document.getElementById('pairSubjectASelect');
            var selectB = document.getElementById('pairSubjectBSelect');
            var klassSelect = document.getElementById('pairKlassSelect');
            if (!selectA || !selectB) return;

            selectA.innerHTML = '<option value="">-- Select Subject --</option>';
            selectB.innerHTML = '<option value="">-- Select Subject --</option>';
            subjects.forEach(function(s) {
                selectA.innerHTML += '<option value="' + s.id + '">' + s.name + '</option>';
                selectB.innerHTML += '<option value="' + s.id + '">' + s.name + '</option>';
            });

            if (klassSelect) {
                klassSelect.innerHTML = '<option value="">All Classes</option>';
                klasses.forEach(function(k) {
                    klassSelect.innerHTML += '<option value="' + k.id + '">' + k.name + '</option>';
                });
            }
        }

        function addSubjectPair() {
            var subjectIdA = document.getElementById('pairSubjectASelect').value;
            var subjectIdB = document.getElementById('pairSubjectBSelect').value;
            var klassId = document.getElementById('pairKlassSelect').value || null;
            var rule = document.getElementById('pairRuleSelect').value;

            if (!subjectIdA) {
                displayMessage('Please select Subject A.', 'error');
                return;
            }
            if (!subjectIdB) {
                displayMessage('Please select Subject B.', 'error');
                return;
            }
            if (subjectIdA === subjectIdB) {
                displayMessage('Subject A and Subject B must be different.', 'error');
                return;
            }

            var btn = document.getElementById('addSubjectPairBtn');
            btn.disabled = true;

            fetch('{{ route("timetable.constraints.save-subject-pair") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    timetable_id: timetableId,
                    subject_id_a: parseInt(subjectIdA),
                    subject_id_b: parseInt(subjectIdB),
                    klass_id: klassId ? parseInt(klassId) : null,
                    rule: rule
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;
                displayMessage(data.message || (data.success ? 'Saved.' : 'Error.'), data.success ? 'success' : 'error');

                if (data.success) {
                    var emptyState = document.getElementById('pairEmptyState');
                    if (emptyState) emptyState.style.display = 'none';
                    var tableWrapper = document.querySelector('#subjectPairContainer .table-responsive');
                    if (tableWrapper) tableWrapper.style.display = '';

                    var tbody = document.getElementById('subjectPairBody');
                    var subjectNameA = '';
                    var subjectNameB = '';
                    subjects.forEach(function(s) {
                        if (s.id == subjectIdA) subjectNameA = s.name;
                        if (s.id == subjectIdB) subjectNameB = s.name;
                    });
                    var klassName = 'All Classes';
                    if (klassId) {
                        klasses.forEach(function(k) { if (k.id == klassId) klassName = k.name; });
                    }
                    var rowCount = tbody.querySelectorAll('tr').length + 1;

                    var newRow = document.createElement('tr');
                    newRow.dataset.constraintId = data.constraint_id || '';
                    newRow.innerHTML =
                        '<td><span class="row-num">' + rowCount + '</span></td>' +
                        '<td><span class="subject-name">' + subjectNameA + '</span></td>' +
                        '<td><span class="subject-name">' + subjectNameB + '</span></td>' +
                        '<td><span class="class-name">' + klassName + '</span></td>' +
                        '<td><span class="rule-badge rule-' + rule + '"><i class="' + (ruleIcons[rule] || 'fas fa-info-circle') + '"></i> ' + (ruleLabels[rule] || rule) + '</span></td>' +
                        '<td class="text-center">' +
                            '<button type="button" class="btn btn-sm btn-outline-danger delete-pair-btn" data-constraint-id="' + (data.constraint_id || '') + '">' +
                                '<i class="fas fa-trash-alt"></i>' +
                            '</button>' +
                        '</td>';
                    tbody.appendChild(newRow);

                    // Reset
                    document.getElementById('pairSubjectASelect').value = '';
                    document.getElementById('pairSubjectBSelect').value = '';
                    document.getElementById('pairKlassSelect').value = '';
                    document.getElementById('pairRuleSelect').value = 'not_same_day';
                }
            })
            .catch(function() {
                btn.disabled = false;
                displayMessage('An error occurred while saving.', 'error');
            });
        }

        // ========================================
        // Period Restriction Tab (CONST-09)
        // ========================================
        var restrictionLabels = {
            'fixed_period': 'Fixed Period',
            'first_or_last': 'First or Last Period',
            'afternoon_only': 'Afternoon Only',
            'reserved_periods': 'Reserved Periods'
        };
        var restrictionIcons = {
            'fixed_period': 'fas fa-thumbtack',
            'first_or_last': 'fas fa-arrows-alt-v',
            'afternoon_only': 'fas fa-sun',
            'reserved_periods': 'fas fa-lock'
        };

        function initializePeriodRestrictionTab() {
            populateRestrictionSubjectDropdown();
            togglePeriodCheckboxes();

            var typeSelect = document.getElementById('restrictionTypeSelect');
            if (typeSelect) {
                typeSelect.addEventListener('change', function() {
                    togglePeriodCheckboxes();
                });
            }

            var addBtn = document.getElementById('addPeriodRestrictionBtn');
            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    addPeriodRestriction();
                });
            }

            var container = document.getElementById('periodRestrictionContainer');
            if (container) {
                container.addEventListener('click', function(e) {
                    var deleteBtn = e.target.closest('.delete-restriction-btn');
                    if (deleteBtn) {
                        var constraintId = deleteBtn.dataset.constraintId;
                        var row = deleteBtn.closest('tr');
                        deleteConstraint(constraintId, function() {
                            row.remove();
                            renumberTableRows('periodRestrictionBody');
                            populateRestrictionSubjectDropdown();
                        });
                    }
                });
            }
        }

        function populateRestrictionSubjectDropdown() {
            var select = document.getElementById('restrictionSubjectSelect');
            if (!select) return;

            var configuredIds = [];
            var rows = document.querySelectorAll('#periodRestrictionBody tr');
            rows.forEach(function(row) {
                var sid = row.dataset.subjectId;
                if (sid) configuredIds.push(parseInt(sid));
            });

            select.innerHTML = '<option value="">-- Select Subject --</option>';
            subjects.forEach(function(s) {
                if (configuredIds.indexOf(s.id) === -1) {
                    select.innerHTML += '<option value="' + s.id + '">' + s.name + '</option>';
                }
            });
        }

        function togglePeriodCheckboxes() {
            var typeSelect = document.getElementById('restrictionTypeSelect');
            var container = document.getElementById('periodCheckboxesContainer');
            if (!typeSelect || !container) return;

            var type = typeSelect.value;
            // Show period checkboxes only for fixed_period and reserved_periods
            if (type === 'fixed_period' || type === 'reserved_periods') {
                container.style.display = '';
            } else {
                container.style.display = 'none';
                // Uncheck all checkboxes
                document.querySelectorAll('.restriction-period-cb').forEach(function(cb) {
                    cb.checked = false;
                });
            }
        }

        // ========================================
        // Teacher Room Assignment Tab (CONST-10)
        // ========================================
        function initializeTeacherRoomAssignmentTab() {
            populateRoomAssignTeacherDropdown();

            var addBtn = document.getElementById('addTeacherRoomAssignmentBtn');
            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    addTeacherRoomAssignment();
                });
            }

            var container = document.getElementById('teacherRoomAssignmentContainer');
            if (container) {
                container.addEventListener('click', function(e) {
                    var deleteBtn = e.target.closest('.delete-room-assign-btn');
                    if (deleteBtn) {
                        var constraintId = deleteBtn.dataset.constraintId;
                        var row = deleteBtn.closest('tr');
                        deleteConstraint(constraintId, function() {
                            row.remove();
                            renumberTableRows('teacherRoomAssignmentBody');
                            populateRoomAssignTeacherDropdown();
                        });
                    }
                });
            }
        }

        function populateRoomAssignTeacherDropdown() {
            var select = document.getElementById('roomAssignTeacherSelect');
            if (!select) return;

            var configuredIds = [];
            var rows = document.querySelectorAll('#teacherRoomAssignmentBody tr');
            rows.forEach(function(row) {
                var tid = row.dataset.teacherId;
                if (tid) configuredIds.push(parseInt(tid));
            });

            select.innerHTML = '<option value="">-- Select Teacher --</option>';
            teachers.forEach(function(t) {
                if (configuredIds.indexOf(t.id) === -1) {
                    select.innerHTML += '<option value="' + t.id + '">' + t.firstname + ' ' + t.lastname + '</option>';
                }
            });
        }

        function addTeacherRoomAssignment() {
            var teacherSelect = document.getElementById('roomAssignTeacherSelect');
            var venueSelect = document.getElementById('roomAssignVenueSelect');
            var teacherId = teacherSelect.value;
            var venueId = venueSelect.value;

            if (!teacherId) {
                displayMessage('Please select a teacher.', 'error');
                return;
            }
            if (!venueId) {
                displayMessage('Please select a venue.', 'error');
                return;
            }

            var btn = document.getElementById('addTeacherRoomAssignmentBtn');
            btn.disabled = true;

            fetch('{{ route("timetable.constraints.save-teacher-room-assignment") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    timetable_id: timetableId,
                    teacher_id: parseInt(teacherId),
                    venue_id: parseInt(venueId)
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;
                var msgType = data.success ? 'success' : 'error';
                if (data.success && data.message && data.message.indexOf('Warning') !== -1) {
                    msgType = 'warning';
                }
                displayMessage(data.message || (data.success ? 'Saved.' : 'Error.'), msgType);

                if (data.success) {
                    var emptyState = document.getElementById('roomAssignEmptyState');
                    if (emptyState) emptyState.style.display = 'none';
                    var tableWrapper = document.querySelector('#teacherRoomAssignmentContainer .table-responsive');
                    if (tableWrapper) tableWrapper.style.display = '';

                    var tbody = document.getElementById('teacherRoomAssignmentBody');
                    var teacherName = '';
                    teachers.forEach(function(t) { if (t.id == teacherId) teacherName = t.firstname + ' ' + t.lastname; });
                    var venueName = '';
                    venues.forEach(function(v) { if (v.id == venueId) venueName = v.name + ' (' + v.type + ')'; });
                    var rowCount = tbody.querySelectorAll('tr').length + 1;

                    var newRow = document.createElement('tr');
                    newRow.dataset.constraintId = data.constraint_id || '';
                    newRow.dataset.teacherId = teacherId;
                    newRow.innerHTML =
                        '<td><span class="row-num">' + rowCount + '</span></td>' +
                        '<td><span class="teacher-name">' + teacherName + '</span></td>' +
                        '<td><span class="venue-badge"><i class="fas fa-home"></i> ' + venueName + '</span></td>' +
                        '<td class="text-center">' +
                            '<button type="button" class="btn btn-sm btn-outline-danger delete-room-assign-btn" data-constraint-id="' + (data.constraint_id || '') + '">' +
                                '<i class="fas fa-trash-alt"></i>' +
                            '</button>' +
                        '</td>';
                    tbody.appendChild(newRow);

                    // Reset
                    teacherSelect.value = '';
                    venueSelect.value = '';
                    populateRoomAssignTeacherDropdown();
                }
            })
            .catch(function() {
                btn.disabled = false;
                displayMessage('An error occurred while saving.', 'error');
            });
        }

        function addPeriodRestriction() {
            var subjectId = document.getElementById('restrictionSubjectSelect').value;
            var restriction = document.getElementById('restrictionTypeSelect').value;

            if (!subjectId) {
                displayMessage('Please select a subject.', 'error');
                return;
            }

            // Collect allowed periods for fixed_period and reserved_periods
            var allowedPeriods = [];
            if (restriction === 'fixed_period' || restriction === 'reserved_periods') {
                document.querySelectorAll('.restriction-period-cb:checked').forEach(function(cb) {
                    allowedPeriods.push(parseInt(cb.value));
                });
                if (allowedPeriods.length === 0) {
                    displayMessage('Please select at least one period.', 'error');
                    return;
                }
            }

            var btn = document.getElementById('addPeriodRestrictionBtn');
            btn.disabled = true;

            var payload = {
                timetable_id: timetableId,
                subject_id: parseInt(subjectId),
                restriction: restriction
            };
            if (allowedPeriods.length > 0) {
                payload.allowed_periods = allowedPeriods;
            }

            fetch('{{ route("timetable.constraints.save-period-restriction") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;
                displayMessage(data.message || (data.success ? 'Saved.' : 'Error.'), data.success ? 'success' : 'error');

                if (data.success) {
                    var emptyState = document.getElementById('restrictionEmptyState');
                    if (emptyState) emptyState.style.display = 'none';
                    var tableWrapper = document.querySelector('#periodRestrictionContainer .table-responsive');
                    if (tableWrapper) tableWrapper.style.display = '';

                    var tbody = document.getElementById('periodRestrictionBody');
                    var subjectName = '';
                    subjects.forEach(function(s) { if (s.id == subjectId) subjectName = s.name; });
                    var rowCount = tbody.querySelectorAll('tr').length + 1;

                    // Derive display periods
                    var displayPeriods = '';
                    if (restriction === 'first_or_last') {
                        displayPeriods = 'P1, P' + periodsPerDay;
                    } else if (restriction === 'afternoon_only') {
                        var start = Math.ceil(periodsPerDay / 2) + 1;
                        var parts = [];
                        for (var i = start; i <= periodsPerDay; i++) parts.push('P' + i);
                        displayPeriods = parts.join(', ');
                    } else {
                        displayPeriods = allowedPeriods.map(function(p) { return 'P' + p; }).join(', ');
                    }

                    // Build period chips
                    var periodChipsHtml = '';
                    var periodsArr = displayPeriods.split(', ');
                    periodsArr.forEach(function(p) {
                        periodChipsHtml += '<span class="period-chip">' + p + '</span>';
                    });

                    var newRow = document.createElement('tr');
                    newRow.dataset.constraintId = data.constraint_id || '';
                    newRow.dataset.subjectId = subjectId;
                    newRow.innerHTML =
                        '<td><span class="row-num">' + rowCount + '</span></td>' +
                        '<td><span class="subject-name">' + subjectName + '</span></td>' +
                        '<td><span class="restriction-badge type-' + restriction + '"><i class="' + (restrictionIcons[restriction] || 'fas fa-info-circle') + '"></i> ' + (restrictionLabels[restriction] || restriction) + '</span></td>' +
                        '<td>' + periodChipsHtml + '</td>' +
                        '<td class="text-center">' +
                            '<button type="button" class="btn btn-sm btn-outline-danger delete-restriction-btn" data-constraint-id="' + (data.constraint_id || '') + '">' +
                                '<i class="fas fa-trash-alt"></i>' +
                            '</button>' +
                        '</td>';
                    tbody.appendChild(newRow);

                    // Reset
                    document.getElementById('restrictionSubjectSelect').value = '';
                    document.getElementById('restrictionTypeSelect').value = 'fixed_period';
                    document.querySelectorAll('.restriction-period-cb').forEach(function(cb) { cb.checked = false; });
                    togglePeriodCheckboxes();
                    populateRestrictionSubjectDropdown();
                }
            })
            .catch(function() {
                btn.disabled = false;
                displayMessage('An error occurred while saving.', 'error');
            });
        }
    </script>
@endsection

@extends('layouts.master')
@section('title')
    Period Settings
@endsection
@section('css')
    <style>
        .period-settings-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .period-settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .period-settings-body {
            padding: 24px;
        }

        /* Card Border */
        .card {
            border: none;
            box-shadow: none;
        }

        /* Tab Styling */
        .nav-tabs-custom {
            border-bottom: 1px solid #e5e7eb;
            gap: 8px;
            position: sticky;
            top: 0;
            z-index: 10;
            background: white;
            padding-top: 8px;
            margin: -8px -16px 0 -16px;
            padding-left: 16px;
            padding-right: 16px;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            background: transparent;
            color: #6b7280;
            font-weight: 500;
            padding: 12px 20px;
            transition: all 0.2s ease;
            border-radius: 0;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #4e73df;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            border-bottom-color: #4e73df;
            background: transparent;
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

        /* Item Type Cards */
        .item-type-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 8px 12px;
            margin-bottom: 8px;
            background: #fafbfc;
        }

        .item-type-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 6px;
        }

        /* Day Preview */
        .day-preview-container {
            display: flex;
            align-items: stretch;
            height: 60px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            background: #f9fafb;
            margin-bottom: 20px;
        }

        .day-preview-period {
            background: #3b82f6;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
            min-width: 40px;
            position: relative;
            border-right: 1px solid rgba(255,255,255,0.2);
        }

        .day-preview-break {
            background: #fef3c7;
            border-left: 1px dashed #f59e0b;
            border-right: 1px dashed #f59e0b;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #92400e;
            min-width: 20px;
        }

        .day-preview-period .period-time {
            font-size: 9px;
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .period-settings-header {
                padding: 20px;
            }

            .period-settings-body {
                padding: 16px;
            }

            .nav-tabs-custom .nav-link {
                padding: 10px 12px;
                font-size: 13px;
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
            Period Settings
        @endslot
    @endcomponent

    <div id="messageContainer"></div>

    <div class="period-settings-container">
        <div class="period-settings-header">
            <h4 class="mb-1 text-white"><i class="bx bx-time-five me-2"></i>Period Settings</h4>
            <p class="mb-0 opacity-75">Configure bell schedule, break intervals, block allocations, and elective coupling for the 6-day rotation cycle</p>
        </div>
        <div class="period-settings-body">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start flex-wrap" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#bellSchedule" role="tab">
                                <i class="fas fa-clock me-2 text-muted"></i>Bell Schedule
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#breaks" role="tab">
                                <i class="fas fa-coffee me-2 text-muted"></i>Breaks
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#blockAllocations" role="tab">
                                <i class="fas fa-th-large me-2 text-muted"></i>Block Allocations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#couplingGroups" role="tab">
                                <i class="fas fa-link me-2 text-muted"></i>Coupling Groups
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        {{-- Bell Schedule Tab --}}
                        <div class="tab-pane active" id="bellSchedule" role="tabpanel">
                            @include('timetable.period-settings._bell-schedule-tab')
                        </div>

                        {{-- Breaks Tab --}}
                        <div class="tab-pane" id="breaks" role="tabpanel">
                            @include('timetable.period-settings._breaks-tab')
                        </div>

                        {{-- Block Allocations Tab --}}
                        <div class="tab-pane" id="blockAllocations" role="tabpanel">
                            @include('timetable.period-settings._block-allocations-tab')
                        </div>

                        {{-- Coupling Groups Tab --}}
                        <div class="tab-pane" id="couplingGroups" role="tabpanel">
                            @include('timetable.period-settings._coupling-groups-tab')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab persistence
            const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
            tabLinks.forEach(tabLink => {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    const activeTabHref = event.target.getAttribute('href');
                    localStorage.setItem('periodSettingsActiveTab', activeTabHref);
                });
            });

            // Check for hash in URL first
            const hash = window.location.hash;
            if (hash) {
                const tabTriggerEl = document.querySelector('.nav-link[href="' + hash + '"]');
                if (tabTriggerEl) {
                    const tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                    history.replaceState(null, null, window.location.pathname);
                }
            } else {
                // Fall back to localStorage
                const activeTab = localStorage.getItem('periodSettingsActiveTab');
                if (activeTab) {
                    const tabTriggerEl = document.querySelector('.nav-link[href="' + activeTab + '"]');
                    if (tabTriggerEl) {
                        const tab = new bootstrap.Tab(tabTriggerEl);
                        tab.show();
                    }
                }
            }

            // Initialize tab functionalities
            initializeBellScheduleTab();
            initializeBreaksTab();
            initializeBlockAllocationsTab();
            initializeCouplingGroupsTab();
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
        // Common Form Submission
        // ========================================
        function submitPeriodSettingsForm(form, url) {
            var formData = new FormData(form);
            var submitBtn = form.querySelector('button[type="submit"]');

            submitBtn.classList.add('loading');
            submitBtn.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;

                if (data.success) {
                    displayMessage(data.message || 'Settings saved successfully.');
                    // Update day preview from server response
                    if (data.daySchedule) {
                        renderDayPreviewFromData(data.daySchedule);
                    }
                } else {
                    displayMessage(data.message || 'Error saving settings.', 'error');
                }
            })
            .catch(function(error) {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                console.error('Error:', error);
                displayMessage('An error occurred while saving settings.', 'error');
            });
        }

        // ========================================
        // Day Preview
        // ========================================
        function renderDayPreview() {
            // Determine which tab is active and read its form state
            var bellScheduleTab = document.getElementById('bellSchedule');
            var isBellScheduleActive = bellScheduleTab && bellScheduleTab.classList.contains('active');

            var periods = [];
            var breaks = [];

            // Always read periods from bell schedule form
            var periodRows = document.querySelectorAll('#periodsContainer .period-row');
            periodRows.forEach(function(row) {
                var periodNum = row.querySelector('input[name$="[period]"]');
                var startTime = row.querySelector('.period-start-time');
                var endTime = row.querySelector('.period-end-time');
                var duration = row.querySelector('.period-duration');

                if (periodNum && startTime && endTime && duration) {
                    periods.push({
                        type: 'period',
                        period: parseInt(periodNum.value),
                        start_time: startTime.value,
                        end_time: endTime.value,
                        duration: parseInt(duration.value) || 40
                    });
                }
            });

            // Read breaks from breaks form
            var breakRows = document.querySelectorAll('#breaksContainer .break-row');
            breakRows.forEach(function(row) {
                var label = row.querySelector('.break-label');
                var afterPeriod = row.querySelector('.break-after-period');
                var duration = row.querySelector('.break-duration');

                if (label && afterPeriod && duration) {
                    breaks.push({
                        after_period: parseInt(afterPeriod.value),
                        duration: parseInt(duration.value) || 10,
                        label: label.value || 'Break'
                    });
                }
            });

            // Build schedule from periods and breaks
            var breaksByAfter = {};
            breaks.forEach(function(b) {
                breaksByAfter[b.after_period] = b;
            });

            // Sort periods by period number
            periods.sort(function(a, b) { return a.period - b.period; });

            var schedule = [];
            periods.forEach(function(period) {
                schedule.push(period);
                if (breaksByAfter[period.period]) {
                    var brk = breaksByAfter[period.period];
                    schedule.push({
                        type: 'break',
                        label: brk.label,
                        duration: brk.duration
                    });
                }
            });

            renderDayPreviewFromSchedule(schedule);
        }

        function renderDayPreviewFromData(daySchedule) {
            // Update all dayPreview containers on the page
            var containers = document.querySelectorAll('#dayPreview');
            containers.forEach(function(container) {
                var html = '';
                daySchedule.forEach(function(item) {
                    if (item.type === 'period') {
                        html += '<div class="day-preview-period" style="flex: ' + item.duration + '">' +
                            '<span>P' + item.period + '</span>' +
                            '<span class="period-time">' + item.start_time + '-' + item.end_time + '</span>' +
                            '</div>';
                    } else {
                        html += '<div class="day-preview-break" style="flex: ' + item.duration + '">' +
                            '<span>' + item.label + '</span>' +
                            '</div>';
                    }
                });
                container.innerHTML = html;
            });
        }

        function renderDayPreviewFromSchedule(schedule) {
            var containers = document.querySelectorAll('#dayPreview');
            containers.forEach(function(container) {
                if (schedule.length === 0) {
                    container.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; width: 100%; color: #9ca3af; font-size: 13px;">No periods configured yet.</div>';
                    return;
                }

                var html = '';
                schedule.forEach(function(item) {
                    if (item.type === 'period') {
                        html += '<div class="day-preview-period" style="flex: ' + item.duration + '">' +
                            '<span>P' + item.period + '</span>' +
                            '<span class="period-time">' + (item.start_time || '') + '-' + (item.end_time || '') + '</span>' +
                            '</div>';
                    } else {
                        html += '<div class="day-preview-break" style="flex: ' + item.duration + '">' +
                            '<span>' + (item.label || 'Break') + '</span>' +
                            '</div>';
                    }
                });
                container.innerHTML = html;
            });
        }

        // ========================================
        // Bell Schedule Tab
        // ========================================
        var periodIndex = {{ count($settings['period_definitions'] ?? []) ?: 1 }};

        function initializeBellScheduleTab() {
            var form = document.getElementById('bellScheduleForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitPeriodSettingsForm(this, this.dataset.url);
                });
            }

            // Add period button
            var addBtn = document.getElementById('addPeriodBtn');
            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    addPeriodRow();
                });
            }

            // Delegate remove period
            var container = document.getElementById('periodsContainer');
            if (container) {
                container.addEventListener('click', function(e) {
                    var btn = e.target.closest('.remove-period-btn');
                    if (btn) {
                        btn.closest('.period-row').remove();
                        renumberPeriods();
                        updateRemoveButtons();
                        renderDayPreview();
                    }
                });

                // Listen for input changes to update preview and recalculate times
                container.addEventListener('input', function(e) {
                    if (e.target.classList.contains('period-duration') || e.target.classList.contains('period-start-time')) {
                        recalculateTimes();
                        renderDayPreview();
                    }
                });
            }
        }

        function addPeriodRow() {
            var container = document.getElementById('periodsContainer');
            var rows = container.querySelectorAll('.period-row');
            var lastRow = rows[rows.length - 1];

            // Calculate next period's start time from last period's end time
            var lastEndTime = '08:10';
            if (lastRow) {
                var endTimeInput = lastRow.querySelector('.period-end-time');
                if (endTimeInput && endTimeInput.value) {
                    lastEndTime = endTimeInput.value;
                }
            }

            var idx = periodIndex++;
            var periodNum = rows.length + 1;
            var duration = 40;
            var endTime = addMinutesToTime(lastEndTime, duration);

            var card = document.createElement('div');
            card.className = 'item-type-card period-row';
            card.dataset.index = idx;
            card.innerHTML =
                '<div class="item-type-header">' +
                    '<div style="flex: 0 0 auto;">' +
                        '<span style="font-weight: 600; color: #374151; font-size: 14px;">Period ' + periodNum + '</span>' +
                        '<input type="hidden" name="period_definitions[' + idx + '][period]" value="' + periodNum + '">' +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger remove-period-btn" title="Remove period">' +
                        '<i class="fas fa-trash-alt"></i>' +
                    '</button>' +
                '</div>' +
                '<div class="row g-3">' +
                    '<div class="col-md-3">' +
                        '<label class="form-label">Start Time</label>' +
                        '<input type="time" class="form-control period-start-time" name="period_definitions[' + idx + '][start_time]" value="' + lastEndTime + '" required>' +
                    '</div>' +
                    '<div class="col-md-3">' +
                        '<label class="form-label">End Time</label>' +
                        '<input type="time" class="form-control period-end-time" name="period_definitions[' + idx + '][end_time]" value="' + endTime + '" required>' +
                    '</div>' +
                    '<div class="col-md-3">' +
                        '<label class="form-label">Duration (min)</label>' +
                        '<input type="number" class="form-control period-duration" name="period_definitions[' + idx + '][duration]" value="' + duration + '" min="20" max="120" required>' +
                        '<div class="form-hint">20-120 minutes</div>' +
                    '</div>' +
                '</div>';

            container.appendChild(card);
            updateRemoveButtons();
            renderDayPreview();
        }

        function renumberPeriods() {
            var rows = document.querySelectorAll('#periodsContainer .period-row');
            rows.forEach(function(row, i) {
                var num = i + 1;
                var label = row.querySelector('.item-type-header span[style]');
                if (label) label.textContent = 'Period ' + num;
                var hidden = row.querySelector('input[name$="[period]"]');
                if (hidden) hidden.value = num;
            });
        }

        function updateRemoveButtons() {
            var rows = document.querySelectorAll('#periodsContainer .period-row');
            var removeButtons = document.querySelectorAll('#periodsContainer .remove-period-btn');
            removeButtons.forEach(function(btn) {
                btn.style.display = rows.length <= 1 ? 'none' : '';
            });
        }

        function recalculateTimes() {
            var rows = document.querySelectorAll('#periodsContainer .period-row');
            if (rows.length === 0) return;

            // Get first period's start time
            var firstStart = rows[0].querySelector('.period-start-time');
            if (!firstStart || !firstStart.value) return;

            var currentTime = firstStart.value;

            // Get breaks for gap insertion
            var breakRows = document.querySelectorAll('#breaksContainer .break-row');
            var breaksByAfter = {};
            breakRows.forEach(function(row) {
                var afterPeriod = row.querySelector('.break-after-period');
                var duration = row.querySelector('.break-duration');
                if (afterPeriod && duration) {
                    breaksByAfter[parseInt(afterPeriod.value)] = parseInt(duration.value) || 10;
                }
            });

            rows.forEach(function(row, i) {
                var startInput = row.querySelector('.period-start-time');
                var endInput = row.querySelector('.period-end-time');
                var durationInput = row.querySelector('.period-duration');
                var periodNum = i + 1;

                var duration = parseInt(durationInput.value) || 40;

                // Set start time (from currentTime)
                startInput.value = currentTime;

                // Calculate end time
                var endTime = addMinutesToTime(currentTime, duration);
                endInput.value = endTime;

                // Move current time forward
                currentTime = endTime;

                // Add break gap if exists
                if (breaksByAfter[periodNum]) {
                    currentTime = addMinutesToTime(currentTime, breaksByAfter[periodNum]);
                }
            });
        }

        // ========================================
        // Breaks Tab
        // ========================================
        var breakIndex = {{ count($settings['break_intervals'] ?? []) }};

        function initializeBreaksTab() {
            var form = document.getElementById('breaksForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitPeriodSettingsForm(this, this.dataset.url);
                });
            }

            // Add break button
            var addBtn = document.getElementById('addBreakBtn');
            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    addBreakRow();
                });
            }

            // Delegate remove break
            var container = document.getElementById('breaksContainer');
            if (container) {
                container.addEventListener('click', function(e) {
                    var btn = e.target.closest('.remove-break-btn');
                    if (btn) {
                        btn.closest('.break-row').remove();
                        renderDayPreview();
                    }
                });

                // Listen for input changes to update preview
                container.addEventListener('input', function() {
                    renderDayPreview();
                });
                container.addEventListener('change', function() {
                    renderDayPreview();
                });
            }
        }

        function addBreakRow() {
            var container = document.getElementById('breaksContainer');
            var idx = breakIndex++;
            var periodCount = document.querySelectorAll('#periodsContainer .period-row').length || {{ count($settings['period_definitions'] ?? []) ?: 7 }};

            var card = document.createElement('div');
            card.className = 'item-type-card break-row';
            card.dataset.index = idx;

            var optionsHtml = '';
            for (var p = 1; p <= periodCount; p++) {
                optionsHtml += '<option value="' + p + '">After Period ' + p + '</option>';
            }

            card.innerHTML =
                '<div class="item-type-header">' +
                    '<div style="flex: 1;">' +
                        '<span style="font-weight: 600; color: #374151; font-size: 14px;">New Break</span>' +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger remove-break-btn" title="Remove break">' +
                        '<i class="fas fa-trash-alt"></i>' +
                    '</button>' +
                '</div>' +
                '<div class="row g-3">' +
                    '<div class="col-md-4">' +
                        '<label class="form-label">Label</label>' +
                        '<input type="text" class="form-control break-label" name="break_intervals[' + idx + '][label]" placeholder="e.g. Tea Break" maxlength="50" required>' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<label class="form-label">After Period</label>' +
                        '<select class="form-select break-after-period" name="break_intervals[' + idx + '][after_period]" required>' +
                            optionsHtml +
                        '</select>' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<label class="form-label">Duration (min)</label>' +
                        '<input type="number" class="form-control break-duration" name="break_intervals[' + idx + '][duration]" value="15" min="5" max="90" required>' +
                        '<div class="form-hint">5-90 minutes</div>' +
                    '</div>' +
                '</div>';

            container.appendChild(card);
            card.querySelector('.break-label').focus();
            renderDayPreview();
        }

        // ========================================
        // Block Allocations Tab
        // ========================================
        function initializeBlockAllocationsTab() {
            var loadBtn = document.getElementById('loadSubjectsBtn');
            var saveBtn = document.getElementById('saveBlockAllocBtn');

            if (loadBtn) {
                loadBtn.addEventListener('click', function() {
                    loadBlockAllocations();
                });
            }

            if (saveBtn) {
                saveBtn.addEventListener('click', function() {
                    saveBlockAllocations();
                });
            }
        }

        function loadBlockAllocations() {
            var timetableId = document.getElementById('blockAllocTimetable').value;
            var klassId = document.getElementById('blockAllocKlass').value;

            if (!timetableId) {
                displayMessage('Please select a timetable.', 'error');
                return;
            }
            if (!klassId) {
                displayMessage('Please select a class.', 'error');
                return;
            }

            var url = '{{ route("timetable.period-settings.get-block-allocations") }}' +
                '?timetable_id=' + timetableId + '&klass_id=' + klassId;

            var loadBtn = document.getElementById('loadSubjectsBtn');
            loadBtn.disabled = true;
            loadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Loading...';

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                loadBtn.disabled = false;
                loadBtn.innerHTML = '<i class="fas fa-search me-1"></i> Load Subjects';

                if (!data.success) {
                    displayMessage(data.message || 'Error loading allocations.', 'error');
                    return;
                }

                var tableDiv = document.getElementById('blockAllocTable');
                var emptyDiv = document.getElementById('blockAllocEmpty');
                var tbody = document.getElementById('blockAllocBody');

                // Build a map of existing allocations by klass_subject_id
                var allocMap = {};
                if (data.allocations && data.allocations.length > 0) {
                    data.allocations.forEach(function(alloc) {
                        allocMap[alloc.klass_subject_id] = alloc;
                    });
                }

                // We need the KlassSubjects for this class -- they are embedded in allocations
                // But we also need to show unallocated subjects.
                // The allocations response returns existing allocations with their klassSubject relationships.
                // We need to fetch ALL KlassSubjects for this class, then merge with existing allocations.

                // For now, if allocations include klass_subject relationship, use those.
                // The controller returns allocations with klassSubject eager loaded.
                // We'll also need to handle the case where no allocations exist yet.

                // Get unique KlassSubjects from response
                var klassSubjects = [];
                var seenIds = {};

                if (data.allocations) {
                    data.allocations.forEach(function(alloc) {
                        if (alloc.klass_subject && !seenIds[alloc.klass_subject_id]) {
                            seenIds[alloc.klass_subject_id] = true;
                            klassSubjects.push({
                                id: alloc.klass_subject_id,
                                subject_name: alloc.klass_subject.grade_subject ?
                                    (alloc.klass_subject.grade_subject.subject ? alloc.klass_subject.grade_subject.subject.name : 'Unknown') : 'Unknown',
                                teacher_name: alloc.klass_subject.teacher ? (alloc.klass_subject.teacher.firstname + ' ' + alloc.klass_subject.teacher.lastname) : 'Unassigned',
                                singles: alloc.singles || 0,
                                doubles: alloc.doubles || 0,
                                triples: alloc.triples || 0
                            });
                        }
                    });
                }

                // Helper to extract teacher name from a KlassSubject object
                function getTeacherName(ks) {
                    if (ks.teacher) {
                        return (ks.teacher.firstname || '') + ' ' + (ks.teacher.lastname || '');
                    }
                    return 'Unassigned';
                }

                if (klassSubjects.length === 0) {
                    // No allocations found -- try to show empty state or subjects without allocations
                    // The getBlockAllocations endpoint only returns existing allocations.
                    // If no allocations exist, we need the KlassSubjects from the class.
                    // We'll populate from the $klasses data passed to view.
                    var selectedKlassId = parseInt(klassId);
                    var klassData = @json($klasses->keyBy('id'));

                    if (klassData[selectedKlassId] && klassData[selectedKlassId].subjects) {
                        klassData[selectedKlassId].subjects.forEach(function(ks) {
                            if (!seenIds[ks.id]) {
                                seenIds[ks.id] = true;
                                klassSubjects.push({
                                    id: ks.id,
                                    subject_name: ks.grade_subject ?
                                        (ks.grade_subject.subject ? ks.grade_subject.subject.name : 'Unknown') : 'Unknown',
                                    teacher_name: getTeacherName(ks),
                                    singles: 0,
                                    doubles: 0,
                                    triples: 0
                                });
                            }
                        });
                    }
                } else {
                    // Also add any KlassSubjects that don't have allocations yet
                    var selectedKlassId = parseInt(klassId);
                    var klassData = @json($klasses->keyBy('id'));

                    if (klassData[selectedKlassId] && klassData[selectedKlassId].subjects) {
                        klassData[selectedKlassId].subjects.forEach(function(ks) {
                            if (!seenIds[ks.id]) {
                                seenIds[ks.id] = true;
                                klassSubjects.push({
                                    id: ks.id,
                                    subject_name: ks.grade_subject ?
                                        (ks.grade_subject.subject ? ks.grade_subject.subject.name : 'Unknown') : 'Unknown',
                                    teacher_name: getTeacherName(ks),
                                    singles: 0,
                                    doubles: 0,
                                    triples: 0
                                });
                            }
                        });
                    }
                }

                if (klassSubjects.length === 0) {
                    tableDiv.style.display = 'none';
                    emptyDiv.style.display = 'block';
                    return;
                }

                // Sort by subject name
                klassSubjects.sort(function(a, b) {
                    return a.subject_name.localeCompare(b.subject_name);
                });

                // Build table rows
                var html = '';
                klassSubjects.forEach(function(ks, idx) {
                    var total = ks.singles + (ks.doubles * 2) + (ks.triples * 3);
                    html +=
                        '<tr data-klass-subject-id="' + ks.id + '">' +
                            '<td>' + (idx + 1) + '</td>' +
                            '<td><span class="subject-name">' + ks.subject_name + '</span></td>' +
                            '<td><span class="teacher-name">' + ks.teacher_name + '</span></td>' +
                            '<td style="text-align:center;"><input type="number" min="0" max="20" class="alloc-input" data-field="singles" value="' + ks.singles + '"></td>' +
                            '<td style="text-align:center;"><input type="number" min="0" max="10" class="alloc-input" data-field="doubles" value="' + ks.doubles + '"></td>' +
                            '<td style="text-align:center;"><input type="number" min="0" max="6" class="alloc-input" data-field="triples" value="' + ks.triples + '"></td>' +
                            '<td class="alloc-total text-center">' + total + '</td>' +
                        '</tr>';
                });

                tbody.innerHTML = html;
                emptyDiv.style.display = 'none';
                tableDiv.style.display = 'block';

                // Update available slots
                if (data.validation) {
                    document.getElementById('blockAllocAvailable').textContent = data.validation.available;
                }

                // Wire up input change events
                var inputs = tbody.querySelectorAll('.alloc-input');
                inputs.forEach(function(input) {
                    input.addEventListener('input', function() {
                        updateBlockAllocTotals();
                    });
                });

                updateBlockAllocTotals();
            })
            .catch(function(error) {
                loadBtn.disabled = false;
                loadBtn.innerHTML = '<i class="fas fa-search me-1"></i> Load Subjects';
                console.error('Error:', error);
                displayMessage('An error occurred while loading subjects.', 'error');
            });
        }

        function updateBlockAllocTotals() {
            var rows = document.querySelectorAll('#blockAllocBody tr');
            var grandTotal = 0;

            rows.forEach(function(row) {
                var singles = parseInt(row.querySelector('[data-field="singles"]').value) || 0;
                var doubles = parseInt(row.querySelector('[data-field="doubles"]').value) || 0;
                var triples = parseInt(row.querySelector('[data-field="triples"]').value) || 0;
                var total = singles + (doubles * 2) + (triples * 3);

                row.querySelector('.alloc-total').textContent = total;
                grandTotal += total;
            });

            var totalSpan = document.getElementById('blockAllocTotal');
            var available = parseInt(document.getElementById('blockAllocAvailable').textContent) || 42;
            var warningDiv = document.getElementById('blockAllocWarning');

            totalSpan.textContent = grandTotal;

            if (grandTotal > available) {
                totalSpan.style.color = '#dc3545';
                warningDiv.style.display = 'block';
            } else {
                totalSpan.style.color = '';
                warningDiv.style.display = 'none';
            }
        }

        function saveBlockAllocations() {
            var timetableId = document.getElementById('blockAllocTimetable').value;
            if (!timetableId) {
                displayMessage('Please select a timetable first.', 'error');
                return;
            }

            var rows = document.querySelectorAll('#blockAllocBody tr');
            if (rows.length === 0) {
                displayMessage('No subjects loaded to save.', 'error');
                return;
            }

            var allocations = [];
            rows.forEach(function(row) {
                allocations.push({
                    klass_subject_id: parseInt(row.dataset.klassSubjectId),
                    singles: parseInt(row.querySelector('[data-field="singles"]').value) || 0,
                    doubles: parseInt(row.querySelector('[data-field="doubles"]').value) || 0,
                    triples: parseInt(row.querySelector('[data-field="triples"]').value) || 0
                });
            });

            var saveBtn = document.getElementById('saveBlockAllocBtn');
            saveBtn.classList.add('loading');
            saveBtn.disabled = true;

            fetch('{{ route("timetable.period-settings.update-block-allocations") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    timetable_id: parseInt(timetableId),
                    allocations: allocations
                })
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                saveBtn.classList.remove('loading');
                saveBtn.disabled = false;

                if (data.success) {
                    var msg = data.message || 'Block allocations saved successfully.';
                    if (data.warnings && data.warnings.length > 0) {
                        msg += ' ' + data.warnings.join(' ');
                        displayMessage(msg, 'warning');
                    } else {
                        displayMessage(msg);
                    }
                } else {
                    displayMessage(data.message || 'Error saving block allocations.', 'error');
                }
            })
            .catch(function(error) {
                saveBtn.classList.remove('loading');
                saveBtn.disabled = false;
                console.error('Error:', error);
                displayMessage('An error occurred while saving block allocations.', 'error');
            });
        }

        // ========================================
        // Coupling Groups Tab
        // ========================================
        var couplingGroupIndex = {{ count($settings['optional_coupling_groups'] ?? []) }};

        function initializeCouplingGroupsTab() {
            var form = document.getElementById('couplingGroupsForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitPeriodSettingsForm(this, this.dataset.url);
                });
            }

            // Add coupling group button
            var addBtn = document.getElementById('addCouplingGroupBtn');
            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    addCouplingGroup();
                });
            }

            // Delegate remove coupling group
            var container = document.getElementById('couplingGroupsContainer');
            if (container) {
                container.addEventListener('click', function(e) {
                    var btn = e.target.closest('.remove-coupling-group-btn');
                    if (btn) {
                        btn.closest('.coupling-group-card').remove();
                        renumberCouplingGroups();
                    }
                });

                // Delegate grade select change
                container.addEventListener('change', function(e) {
                    if (e.target.classList.contains('coupling-grade-select')) {
                        updateCouplingSubjects(e.target);
                    }
                });
            }
        }

        function addCouplingGroup() {
            var container = document.getElementById('couplingGroupsContainer');
            var idx = couplingGroupIndex++;
            var groupNum = container.querySelectorAll('.coupling-group-card').length + 1;

            // Build grade options
            var grades = @json($klasses->pluck('grade')->unique('id')->filter()->sortBy('sequence')->values());
            var gradeOptions = '<option value="">-- Select Grade --</option>';
            grades.forEach(function(grade) {
                gradeOptions += '<option value="' + grade.id + '">' + grade.name + '</option>';
            });

            var card = document.createElement('div');
            card.className = 'coupling-group-card';
            card.dataset.index = idx;
            card.innerHTML =
                '<div class="group-card-header item-type-header">' +
                    '<div class="d-flex align-items-center">' +
                        '<span class="group-badge">' + groupNum + '</span>' +
                        '<span class="group-title">Group ' + groupNum + '</span>' +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger remove-coupling-group-btn" title="Remove group">' +
                        '<i class="fas fa-trash-alt"></i>' +
                    '</button>' +
                '</div>' +
                '<div class="group-card-body">' +
                    '<div class="row g-3">' +
                        '<div class="col-md-4">' +
                            '<label class="form-label">Group Label</label>' +
                            '<input type="text" class="form-control" name="coupling_groups[' + idx + '][label]" placeholder="e.g. Form 2 Optionals" maxlength="100" required>' +
                        '</div>' +
                        '<div class="col-md-4">' +
                            '<label class="form-label">Grade</label>' +
                            '<select class="form-select coupling-grade-select" name="coupling_groups[' + idx + '][grade_id]" required>' +
                                gradeOptions +
                            '</select>' +
                        '</div>' +
                        '<div class="col-md-4">' +
                            '<label class="form-label">Optional Subjects</label>' +
                            '<select class="form-select coupling-subjects-select" name="coupling_groups[' + idx + '][optional_subject_ids][]" multiple required>' +
                            '</select>' +
                            '<div class="form-hint">Hold Ctrl/Cmd to select multiple</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="block-alloc-row">' +
                    '<div class="block-alloc-label">Block Allocation per Cycle</div>' +
                    '<div class="row g-3">' +
                        '<div class="col-md-4">' +
                            '<div class="block-field">' +
                                '<span class="block-icon single-icon"><i class="fas fa-square"></i></span>' +
                                '<input type="number" class="block-input" name="coupling_groups[' + idx + '][singles]" value="0" min="0" max="20">' +
                                '<span class="block-type-label">Singles</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="col-md-4">' +
                            '<div class="block-field">' +
                                '<span class="block-icon double-icon"><i class="fas fa-th-large"></i></span>' +
                                '<input type="number" class="block-input" name="coupling_groups[' + idx + '][doubles]" value="0" min="0" max="10">' +
                                '<span class="block-type-label">Doubles</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="col-md-4">' +
                            '<div class="block-field">' +
                                '<span class="block-icon triple-icon"><i class="fas fa-th"></i></span>' +
                                '<input type="number" class="block-input" name="coupling_groups[' + idx + '][triples]" value="0" min="0" max="6">' +
                                '<span class="block-type-label">Triples</span>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';

            container.appendChild(card);
            card.querySelector('input[name$="[label]"]').focus();
        }

        function renumberCouplingGroups() {
            var cards = document.querySelectorAll('#couplingGroupsContainer .coupling-group-card');
            cards.forEach(function(card, i) {
                var badge = card.querySelector('.group-badge');
                var title = card.querySelector('.group-title');
                if (badge) badge.textContent = (i + 1);
                if (title) title.textContent = 'Group ' + (i + 1);
            });
        }

        function updateCouplingSubjects(gradeSelect) {
            var gradeId = gradeSelect.value;
            var card = gradeSelect.closest('.coupling-group-card');
            var subjectsSelect = card.querySelector('.coupling-subjects-select');

            subjectsSelect.innerHTML = '';

            if (!gradeId || !optionalSubjectsByGrade[gradeId]) {
                return;
            }

            optionalSubjectsByGrade[gradeId].forEach(function(os) {
                var option = document.createElement('option');
                option.value = os.id;
                option.textContent = os.name || os.subject || 'Unknown';
                subjectsSelect.appendChild(option);
            });
        }

        // ========================================
        // Time Helpers
        // ========================================
        function addMinutesToTime(timeStr, minutes) {
            var parts = timeStr.split(':');
            var hours = parseInt(parts[0]);
            var mins = parseInt(parts[1]);
            mins += minutes;
            hours += Math.floor(mins / 60);
            mins = mins % 60;
            hours = hours % 24;
            return padZero(hours) + ':' + padZero(mins);
        }

        function padZero(n) {
            return n < 10 ? '0' + n : '' + n;
        }
    </script>
@endsection

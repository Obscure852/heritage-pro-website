@extends('layouts.crm')

@section('title', 'Team Attendance')
@section('crm_heading', 'Team Attendance')
@section('crm_subheading', 'Two-week attendance overview grouped by department. Click a cell to view or edit a record.')

@section('crm_header_stats')
    @include('crm.partials.header-stat', ['value' => $teamGrid['total_users'], 'label' => 'Staff'])
    @include('crm.partials.header-stat', [
        'value' => $gridStart->format('d M') . ' – ' . $gridEnd->format('d M'),
        'label' => 'Date Range',
    ])
@endsection

@section('content')
    <div class="crm-stack">
        {{-- Filter Card --}}
        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find staff attendance</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.attendance.grid') }}" class="crm-filter-form">
                <input type="hidden" name="week" value="{{ $weekOffset }}">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="search">Search</label>
                        <input id="search" name="search" value="{{ $filters['search'] }}" placeholder="Name or email">
                    </div>
                    <div class="crm-field">
                        <label for="department_ids">Department</label>
                        <select id="department_ids" name="department_ids[]">
                            <option value="">All departments</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" @selected(in_array((string) $dept->id, $filters['department_ids']))>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="code_ids">Attendance Code</label>
                        <select id="code_ids" name="code_ids[]">
                            <option value="">All codes</option>
                            @foreach ($codes as $code)
                                <option value="{{ $code->id }}" @selected(in_array((string) $code->id, $filters['code_ids']))>{{ $code->code }} — {{ $code->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="show_weekends">Show Weekends</label>
                        <select id="show_weekends" name="show_weekends">
                            <option value="1" @selected($filters['show_weekends'])>Yes</option>
                            <option value="0" @selected(! $filters['show_weekends'])>No</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.attendance.grid') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply</button>

                    <div class="dropdown" style="margin-left: auto;">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="display: inline-flex; align-items: center; gap: 6px;">
                            <i class="bx bx-bar-chart-alt-2"></i> Reports
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 260px;">
                            <li><a class="dropdown-item" href="{{ route('crm.attendance.reports.show', 'daily-summary') }}"><i class="bx bx-calendar-check me-2" style="color: #64748b;"></i> Daily Summary</a></li>
                            <li><a class="dropdown-item" href="{{ route('crm.attendance.reports.show', 'monthly-register') }}"><i class="bx bx-calendar me-2" style="color: #64748b;"></i> Monthly Register</a></li>
                            <li><a class="dropdown-item" href="{{ route('crm.attendance.reports.show', 'hours-worked') }}"><i class="bx bx-time-five me-2" style="color: #64748b;"></i> Hours Worked</a></li>
                            <li><a class="dropdown-item" href="{{ route('crm.attendance.reports.show', 'late-arrivals') }}"><i class="bx bx-error-circle me-2" style="color: #64748b;"></i> Late Arrivals</a></li>
                            <li><a class="dropdown-item" href="{{ route('crm.attendance.reports.show', 'absenteeism') }}"><i class="bx bx-user-x me-2" style="color: #64748b;"></i> Absenteeism</a></li>
                            <li><a class="dropdown-item" href="{{ route('crm.attendance.reports.show', 'biometric-audit') }}"><i class="bx bx-chip me-2" style="color: #64748b;"></i> Biometric Audit</a></li>
                        </ul>
                    </div>
                </div>
            </form>
        </section>

        {{-- Grid Table --}}
        <section class="crm-card">
            <div class="crm-card-title" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p class="crm-kicker">{{ $gridStart->format('d M') }} – {{ $gridEnd->format('d M Y') }}</p>
                    <h2>Attendance Grid</h2>
                </div>
                <div style="display: inline-flex; align-items: center; gap: 8px;">
                    <a href="{{ route('crm.attendance.grid', ['week' => $weekOffset - 1]) }}" class="btn btn-light crm-btn-light" style="padding: 6px 12px; font-size: 13px;"><i class="bx bx-chevron-left"></i> Prev</a>
                    @if ($weekOffset !== 0)
                        <a href="{{ route('crm.attendance.grid') }}" class="btn btn-light crm-btn-light" style="padding: 6px 12px; font-size: 13px;">Today</a>
                    @endif
                    <a href="{{ route('crm.attendance.grid', ['week' => $weekOffset + 1]) }}" class="btn btn-light crm-btn-light" style="padding: 6px 12px; font-size: 13px;">Next <i class="bx bx-chevron-right"></i></a>
                </div>
            </div>

            @include('crm.attendance.partials.legend', ['codes' => $codes])

            @if ($teamGrid['total_users'] === 0)
                <div class="crm-empty">No staff found matching the current filters.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table" style="table-layout: fixed;">
                        <thead>
                            <tr>
                                <th style="min-width: 180px; position: sticky; left: 0; background: #fff; z-index: 2;">Employee</th>
                                @foreach ($teamGrid['date_headers'] as $header)
                                    @if ($filters['show_weekends'] || ! $header['is_weekend'])
                                        <th style="text-align: center; min-width: 54px; font-size: 11px; padding: 8px 4px;
                                            {{ $header['is_today'] ? 'background: rgba(37,99,235,0.06);' : '' }}
                                            {{ $header['is_weekend'] ? 'background: #f8fafc;' : '' }}
                                            {{ $header['is_holiday'] ? 'background: rgba(101,89,204,0.06);' : '' }}">
                                            {{ $header['day_label'] }}<br>{{ $header['day_number'] }}
                                        </th>
                                    @endif
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teamGrid['departments'] as $group)
                                <tr>
                                    <td colspan="{{ 1 + count(array_filter($teamGrid['date_headers'], fn ($h) => $filters['show_weekends'] || ! $h['is_weekend'])) }}"
                                        style="background: #f8fafc; font-weight: 600; font-size: 13px; padding: 10px 14px; color: #334155; border-bottom: 1px solid #e5e7eb;">
                                        {{ $group['department_name'] }} <span class="crm-muted" style="font-weight: 400;">({{ $group['staff_count'] }} staff)</span>
                                    </td>
                                </tr>

                                @foreach ($group['users'] as $row)
                                    <tr>
                                        <td style="position: sticky; left: 0; background: #fff; z-index: 1; padding: 8px 14px; white-space: nowrap; font-size: 13px;">
                                            {{ $row['user']->name }}
                                        </td>
                                        @foreach ($row['days'] as $i => $cell)
                                            @if ($filters['show_weekends'] || ! $teamGrid['date_headers'][$i]['is_weekend'])
                                                @include('crm.attendance.partials.grid-cell', ['cell' => $cell, 'canEdit' => $canEdit])
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    @if ($canEdit)
        @include('crm.attendance.partials.record-panel', ['codes' => $codes])
    @endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var panel = document.getElementById('crm-record-panel');
    var backdrop = document.getElementById('crm-record-panel-backdrop');
    var panelClose = document.getElementById('crm-panel-close');
    var panelLoading = document.getElementById('crm-panel-loading');
    var panelContent = document.getElementById('crm-panel-content');
    var panelMeta = document.getElementById('crm-panel-meta');
    var panelDate = document.getElementById('crm-panel-date');
    var panelUserName = document.getElementById('crm-panel-user-name');
    var editSection = document.getElementById('crm-panel-edit-section');
    var editForm = document.getElementById('crm-panel-edit-form');
    var currentRecordId = null;

    if (!panel) return;

    function openPanel() {
        panel.classList.add('is-open');
        backdrop.classList.add('is-visible');
    }

    function closePanel() {
        panel.classList.remove('is-open');
        backdrop.classList.remove('is-visible');
        currentRecordId = null;
    }

    if (panelClose) panelClose.addEventListener('click', closePanel);
    if (backdrop) backdrop.addEventListener('click', closePanel);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePanel();
    });

    function formatMinutes(m) {
        if (!m) return '—';
        return Math.floor(m / 60) + 'h ' + (m % 60) + 'm';
    }

    function loadRecord(recordId) {
        currentRecordId = recordId;
        panelLoading.style.display = 'block';
        panelContent.style.display = 'none';
        openPanel();

        fetch('/crm/attendance/records/' + recordId, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            panelLoading.style.display = 'none';
            panelContent.style.display = 'block';
            panelDate.textContent = data.date;
            panelUserName.textContent = data.user_name || 'Record Detail';

            var rows = [
                ['Code', data.code ? '<span class="crm-pill" style="background:' + data.code.color + '20;color:' + data.code.color + ';font-size:11px;padding:4px 10px;font-weight:600;">' + data.code.code + ' — ' + data.code.label + '</span>' : '—'],
                ['Clock In', data.clocked_in_at || '—'],
                ['Clock Out', data.clocked_out_at || '—'],
                ['Total', formatMinutes(data.total_minutes)],
                ['Source', data.source || '—'],
                ['Late', data.is_late ? 'Yes' : 'No'],
                ['Early Out', data.is_early_out ? 'Yes' : 'No'],
                ['Overtime', formatMinutes(data.overtime_minutes)],
                ['Status', data.status || '—'],
            ];

            if (data.clock_in_note) rows.push(['In Note', data.clock_in_note]);
            if (data.clock_out_note) rows.push(['Out Note', data.clock_out_note]);
            if (data.auto_closed) rows.push(['Auto-closed', 'Yes — clock-out was recorded automatically']);

            panelMeta.innerHTML = rows.map(function (r) {
                return '<div class="crm-meta-row"><span>' + r[0] + '</span><strong>' + r[1] + '</strong></div>';
            }).join('');

            var pendingEl = document.getElementById('crm-panel-pending-corrections');
            if (pendingEl) {
                pendingEl.style.display = data.pending_corrections > 0 ? 'block' : 'none';
            }

            if (editSection) {
                editSection.style.display = 'block';
                if (data.code) {
                    document.getElementById('panel-code').value = data.code.id;
                }
            }
        })
        .catch(function () {
            panelLoading.style.display = 'none';
            panelContent.style.display = 'block';
            panelMeta.innerHTML = '<div class="crm-empty">Unable to load record.</div>';
        });
    }

    document.querySelectorAll('.crm-attendance-cell').forEach(function (cell) {
        cell.addEventListener('click', function () {
            var recordId = cell.getAttribute('data-record-id');
            if (recordId) loadRecord(recordId);
        });
    });

    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!currentRecordId) return;

            var submitBtn = editForm.querySelector('button[type="submit"].btn-loading');
            if (submitBtn) { submitBtn.classList.add('loading'); submitBtn.disabled = true; }

            var formData = {
                attendance_code_id: document.getElementById('panel-code').value,
                clocked_in_at: document.getElementById('panel-clock-in').value || null,
                clocked_out_at: document.getElementById('panel-clock-out').value || null,
                clock_in_note: document.getElementById('panel-note-in').value || null,
                clock_out_note: document.getElementById('panel-note-out').value || null,
            };

            fetch('/crm/attendance/records/' + currentRecordId, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin',
                body: JSON.stringify(formData)
            })
            .then(function (r) {
                if (!r.ok) throw new Error('Update failed');
                return r.json();
            })
            .then(function () {
                window.location.reload();
            })
            .catch(function () {
                if (submitBtn) { submitBtn.classList.remove('loading'); submitBtn.disabled = false; }
            });
        });
    }
});
</script>
@endpush

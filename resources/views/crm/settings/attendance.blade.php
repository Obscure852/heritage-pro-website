@extends('layouts.crm')

@section('title', 'Attendance Settings')
@section('crm_heading', 'Attendance Settings')
@section('crm_subheading', 'Manage attendance codes, shift schedules, public holidays, and biometric devices from one page.')

@section('crm_header_stats')
    @include('crm.partials.header-stat', ['value' => $codes->where('is_active', true)->count(), 'label' => 'Active Codes'])
    @include('crm.partials.header-stat', ['value' => $shifts->where('is_active', true)->count(), 'label' => 'Active Shifts'])
    @include('crm.partials.header-stat', ['value' => $holidays->where('is_active', true)->count(), 'label' => 'Holidays'])
    @include('crm.partials.header-stat', ['value' => $devices->where('is_active', true)->count(), 'label' => 'Devices'])
@endsection

@section('content')
    <div class="crm-stack">
        {{-- Tab navigation --}}
        <div class="crm-tabs crm-tabs-top">
            <a href="{{ route('crm.settings.attendance.index', ['tab' => 'codes']) }}" @class(['crm-tab', 'is-active' => $activeTab === 'codes'])>
                <i class="bx bx-palette"></i> <span>Codes</span>
            </a>
            <a href="{{ route('crm.settings.attendance.index', ['tab' => 'shifts']) }}" @class(['crm-tab', 'is-active' => $activeTab === 'shifts'])>
                <i class="bx bx-time-five"></i> <span>Shifts</span>
            </a>
            <a href="{{ route('crm.settings.attendance.index', ['tab' => 'holidays']) }}" @class(['crm-tab', 'is-active' => $activeTab === 'holidays'])>
                <i class="bx bx-calendar-star"></i> <span>Holidays</span>
            </a>
            <a href="{{ route('crm.settings.attendance.index', ['tab' => 'devices']) }}" @class(['crm-tab', 'is-active' => $activeTab === 'devices'])>
                <i class="bx bx-chip"></i> <span>Devices</span>
            </a>
            <a href="{{ route('crm.settings.attendance.index', ['tab' => 'docs']) }}" @class(['crm-tab', 'is-active' => $activeTab === 'docs'])>
                <i class="bx bx-book-open"></i> <span>Setup Guide</span>
            </a>
        </div>

        {{-- ═══════════════════ CODES TAB ═══════════════════ --}}
        @if ($activeTab === 'codes')
            {{-- Widget visibility toggles --}}
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Widget Visibility</p>
                        <h2>Clock Widget Settings</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('crm.settings.attendance.widget.update') }}" class="crm-form">
                    @csrf @method('PATCH')
                    <div style="display: flex; gap: 32px; flex-wrap: wrap;">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="show_topbar_clock" value="1" class="form-check-input" id="toggle-topbar" role="switch" @checked($attendanceSettings->show_topbar_clock)>
                            <label class="form-check-label" for="toggle-topbar" style="font-size: 13px; font-weight: 500;">Show clock widget in top navigation bar</label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="show_dashboard_clock" value="1" class="form-check-input" id="toggle-dashboard" role="switch" @checked($attendanceSettings->show_dashboard_clock)>
                            <label class="form-check-label" for="toggle-dashboard" style="font-size: 13px; font-weight: 500;">Show clock widget on dashboard</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-loading" style="padding: 6px 16px; font-size: 13px;">
                            <span class="btn-text"><i class="fas fa-save"></i> Save</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Saving...</span>
                        </button>
                    </div>
                </form>
            </section>

            @include('crm.partials.helper-text', [
                'title' => 'Attendance Codes',
                'content' => 'Codes appear as badges on the attendance grid. System codes cannot be deleted but can be renamed or recoloured.',
            ])

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">All codes</p>
                        <h2>Attendance Code List</h2>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#codeModal" onclick="resetCodeModal()">
                        <i class="bx bx-plus"></i> New Code
                    </button>
                </div>

                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">Color</th>
                                <th>Code</th>
                                <th>Label</th>
                                <th>Category</th>
                                <th>Working %</th>
                                <th>System</th>
                                <th>Active</th>
                                <th>Order</th>
                                <th class="crm-table-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($codes as $code)
                                <tr>
                                    <td><span style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; background: {{ $code->color }};"></span></td>
                                    <td><strong>{{ $code->code }}</strong></td>
                                    <td>{{ $code->label }}</td>
                                    <td><span class="crm-pill muted">{{ ucfirst($code->category) }}</span></td>
                                    <td>{{ number_format((float) $code->counts_as_working * 100) }}%</td>
                                    <td>@if ($code->is_system) <i class="bx bx-lock-alt" style="color: #94a3b8;"></i> @endif</td>
                                    <td>
                                        @if ($code->is_active)
                                            <span class="crm-pill success">Active</span>
                                        @else
                                            <span class="crm-pill muted">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $code->sort_order }}</td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            <button type="button" class="btn crm-icon-action" title="Edit"
                                                    data-bs-toggle="modal" data-bs-target="#codeModal"
                                                    onclick="editCodeModal({{ $code->id }}, '{{ $code->code }}', '{{ addslashes($code->label) }}', '{{ $code->color }}', '{{ $code->category }}', {{ $code->counts_as_working }}, {{ $code->is_active ? 'true' : 'false' }}, {{ $code->sort_order }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if (! $code->is_system)
                                                <form method="POST" action="{{ route('crm.settings.attendance.codes.destroy', $code) }}" class="crm-inline-form"
                                                      onsubmit="return confirm('Deactivate this code?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn crm-icon-action crm-icon-danger" title="Deactivate"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        {{-- ═══════════════════ SHIFTS TAB ═══════════════════ --}}
        @if ($activeTab === 'shifts')
            @include('crm.partials.helper-text', [
                'title' => 'Shift Schedules',
                'content' => 'Each shift defines working days and hours. The system uses shifts to detect late arrivals, early departures, and overtime.',
            ])

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">All shifts</p>
                        <h2>Shift List</h2>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#shiftModal">
                        <i class="bx bx-plus"></i> New Shift
                    </button>
                </div>

                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr><th>Name</th><th>Default</th><th>Schedule</th><th>Grace</th><th>Users</th><th>Active</th><th class="crm-table-actions">Actions</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($shifts as $shift)
                                @php
                                    $workingDays = $shift->days->where('is_working_day', true);
                                    $dayNames = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
                                    $scheduleLabel = $workingDays->map(fn ($d) => $dayNames[$d->day_of_week] ?? '?')->implode(', ');
                                    $timeLabel = $workingDays->isNotEmpty() ? $workingDays->first()->start_time . '–' . $workingDays->first()->end_time : '—';
                                @endphp
                                <tr>
                                    <td><strong>{{ $shift->name }}</strong></td>
                                    <td>@if ($shift->is_default) <i class="bx bxs-star" style="color: #f7b84b;"></i> @endif</td>
                                    <td><span class="crm-muted" style="font-size: 12px;">{{ $scheduleLabel }} {{ $timeLabel }}</span></td>
                                    <td>{{ $shift->grace_minutes }}m</td>
                                    <td>{{ $shift->users_count }}</td>
                                    <td>
                                        @if ($shift->is_active) <span class="crm-pill success">Active</span>
                                        @else <span class="crm-pill muted">Inactive</span> @endif
                                    </td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            <button type="button" class="btn crm-icon-action" title="Edit"
                                                    data-bs-toggle="modal" data-bs-target="#shiftModal"
                                                    onclick="editShiftModal({{ $shift->id }}, '{{ addslashes($shift->name) }}', {{ $shift->grace_minutes }}, {{ $shift->early_out_minutes }}, {{ $shift->overtime_after_minutes }}, '{{ $shift->earliest_clock_in ?? '' }}', '{{ $shift->latest_clock_in ?? '' }}', {{ $shift->is_default ? 'true' : 'false' }}, {{ $shift->is_active ? 'true' : 'false' }}, {{ json_encode($shift->days->keyBy('day_of_week')->map(fn ($d) => ['start_time' => substr($d->start_time, 0, 5), 'end_time' => substr($d->end_time, 0, 5), 'is_working_day' => $d->is_working_day])) }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if (! $shift->is_default)
                                                <form method="POST" action="{{ route('crm.settings.attendance.shifts.destroy', $shift) }}" class="crm-inline-form"
                                                      onsubmit="return confirm('Deactivate this shift?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn crm-icon-action crm-icon-danger" title="Deactivate"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        {{-- ═══════════════════ HOLIDAYS TAB ═══════════════════ --}}
        @if ($activeTab === 'holidays')
            @include('crm.partials.helper-text', [
                'title' => 'Public Holidays',
                'content' => 'When a holiday falls on a working day, the system automatically applies the H (Holiday) code. Scoped holidays only affect a specific department or shift.',
            ])

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">All holidays</p>
                        <h2>Holiday List</h2>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#holidayModal">
                        <i class="bx bx-plus"></i> New Holiday
                    </button>
                </div>

                @if ($holidays->isEmpty())
                    <div class="crm-empty">No holidays have been created yet.</div>
                @else
                    <div class="crm-table-wrap">
                        <table class="crm-table">
                            <thead>
                                <tr><th>Name</th><th>Date</th><th>Recurring</th><th>Scope</th><th>Active</th><th class="crm-table-actions">Actions</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($holidays as $holiday)
                                    <tr>
                                        <td><strong>{{ $holiday->name }}</strong></td>
                                        <td>{{ $holiday->date->format('d M Y') }}</td>
                                        <td>
                                            @if ($holiday->is_recurring) <span class="crm-pill primary">Recurring</span>
                                            @else <span class="crm-pill muted">One-off</span> @endif
                                        </td>
                                        <td>{{ ucfirst($holiday->applies_to) }}{{ $holiday->scope_id ? ' #' . $holiday->scope_id : '' }}</td>
                                        <td>
                                            @if ($holiday->is_active) <span class="crm-pill success">Active</span>
                                            @else <span class="crm-pill muted">Inactive</span> @endif
                                        </td>
                                        <td class="crm-table-actions">
                                            <div class="crm-action-row">
                                                <form method="POST" action="{{ route('crm.settings.attendance.holidays.destroy', $holiday) }}" class="crm-inline-form"
                                                      onsubmit="return confirm('Delete this holiday?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn crm-icon-action crm-icon-danger" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endif

        {{-- ═══════════════════ DEVICES TAB ═══════════════════ --}}
        @if ($activeTab === 'devices')
            @include('crm.partials.helper-text', [
                'title' => 'Biometric Devices',
                'content' => 'Register ZKTeco, Hikvision, Suprema, Anviz, or other biometric devices. For ZKTeco ADMS devices, set the push server URL on the device to: ' . url('/api/crm/attendance/iclock/cdata') . '. For other brands, use the JSON push endpoint with Sanctum token authentication.',
            ])

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Registered devices</p>
                        <h2>Device List</h2>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deviceModal">
                        <i class="bx bx-plus"></i> New Device
                    </button>
                </div>

                @if ($devices->isEmpty())
                    <div class="crm-empty">No biometric devices have been registered yet.</div>
                @else
                    <div class="crm-table-wrap">
                        <table class="crm-table">
                            <thead>
                                <tr><th>Name</th><th>Brand / Model</th><th>Identifier</th><th>IP Address</th><th>Status</th><th>Last Heartbeat</th><th>Events</th><th class="crm-table-actions">Actions</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($devices as $device)
                                    <tr>
                                        <td><strong>{{ $device->name }}</strong><br><span class="crm-muted" style="font-size: 11px;">{{ $device->location ?? '' }}</span></td>
                                        <td><span class="crm-pill primary" style="font-size: 10px;">{{ $device->brandLabel() }}</span><br><span class="crm-muted" style="font-size: 11px;">{{ $device->model ?? '—' }}</span></td>
                                        <td><code style="font-size: 11px; background: #f1f5f9; padding: 2px 6px; border-radius: 3px;">{{ $device->device_identifier }}</code></td>
                                        <td style="font-size: 12px;">{{ $device->ip_address ?? '—' }}{{ $device->port ? ':' . $device->port : '' }}</td>
                                        <td>
                                            @if (! $device->is_active) <span class="crm-pill muted">Inactive</span>
                                            @elseif ($device->isOnline()) <span class="crm-pill success">Online</span>
                                            @else <span class="crm-pill danger">Offline</span> @endif
                                        </td>
                                        <td>{{ $device->last_heartbeat_at ? $device->last_heartbeat_at->diffForHumans() : 'Never' }}</td>
                                        <td>{{ number_format($device->logs_count) }}</td>
                                        <td class="crm-table-actions">
                                            <div class="crm-action-row">
                                                <button type="button" class="btn crm-icon-action" title="Edit"
                                                        data-bs-toggle="modal" data-bs-target="#deviceEditModal-{{ $device->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="{{ route('crm.settings.attendance.devices.regenerate-token', $device) }}" class="crm-inline-form"
                                                      onsubmit="return confirm('Regenerate API token? The old token will stop working.')">
                                                    @csrf
                                                    <button type="submit" class="btn crm-icon-action" title="Regenerate Token"><i class="bx bx-key"></i></button>
                                                </form>
                                                <form method="POST" action="{{ route('crm.settings.attendance.devices.destroy', $device) }}" class="crm-inline-form"
                                                      onsubmit="return confirm('Permanently delete this device and all its event logs? This cannot be undone.')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn crm-icon-action crm-icon-danger" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            {{-- Per-device edit modals --}}
            @foreach ($devices as $device)
                <div class="modal fade" id="deviceEditModal-{{ $device->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Device — {{ $device->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" id="deviceEditForm-{{ $device->id }}" action="{{ route('crm.settings.attendance.devices.update', $device) }}" class="crm-form">
                                    @csrf @method('PUT')

                                    <p class="crm-kicker" style="margin-bottom: 12px;">Device Identity</p>
                                    <div class="crm-field-grid">
                                        <div class="crm-field"><label>Device Name</label><input name="name" required maxlength="100" value="{{ $device->name }}" placeholder="e.g. Main Entrance Scanner"></div>
                                        <div class="crm-field"><label>Brand</label><input value="{{ $device->brandLabel() }}" disabled class="form-control" style="background: #f8fafc;"></div>
                                        <div class="crm-field"><label>Model</label><input name="model" maxlength="80" value="{{ $device->model }}" placeholder="e.g. SpeedFace-V5L"></div>
                                        <div class="crm-field"><label>Identifier</label><input value="{{ $device->device_identifier }}" disabled class="form-control" style="background: #f8fafc;"></div>
                                        <div class="crm-field"><label>Serial Number</label><input value="{{ $device->serial_number }}" disabled class="form-control" style="background: #f8fafc;"></div>
                                        <div class="crm-field"><label>Location</label><input name="location" maxlength="200" value="{{ $device->location }}" placeholder="e.g. Main building, ground floor"></div>
                                    </div>

                                    <p class="crm-kicker" style="margin: 20px 0 12px;">Network Configuration</p>
                                    <div class="crm-field-grid">
                                        <div class="crm-field"><label>IP Address</label><input name="ip_address" maxlength="45" value="{{ $device->ip_address }}" placeholder="e.g. 192.168.1.201"></div>
                                        <div class="crm-field"><label>Port</label><input name="port" type="number" min="1" max="65535" value="{{ $device->port }}" placeholder="e.g. 80"></div>
                                        <div class="crm-field"><label>Communication Key</label><input name="communication_key" maxlength="100" value="{{ $device->communication_key }}" placeholder="e.g. 0"></div>
                                        <div class="crm-field">
                                            <label>Direction</label>
                                            <select name="direction" required>
                                                @foreach (config('heritage_crm.attendance.device_directions', []) as $key => $label)
                                                    <option value="{{ $key }}" @selected($device->direction === $key)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <p class="crm-kicker" style="margin: 20px 0 12px;">Timing & Thresholds</p>
                                    <div class="crm-field-grid">
                                        <div class="crm-field"><label>Timezone Offset</label><input name="timezone" maxlength="40" value="{{ $device->timezone }}" placeholder="e.g. +2"></div>
                                        <div class="crm-field"><label>Heartbeat Interval (s)</label><input name="heartbeat_interval" type="number" min="10" max="3600" value="{{ $device->heartbeat_interval }}" placeholder="e.g. 60"></div>
                                        <div class="crm-field"><label>Push Interval (s)</label><input name="push_interval" type="number" min="5" max="3600" value="{{ $device->push_interval }}" placeholder="e.g. 30"></div>
                                        <div class="crm-field"><label>Min Confidence (0–1)</label><input name="min_confidence" type="number" step="0.01" min="0" max="1" required value="{{ $device->min_confidence }}" placeholder="e.g. 0.80"></div>
                                    </div>

                                    <div style="margin-top: 16px;">
                                        <div class="form-check">
                                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="d-edit-active-{{ $device->id }}" @checked($device->is_active)>
                                            <label class="form-check-label" for="d-edit-active-{{ $device->id }}">Active</label>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light crm-btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" form="deviceEditForm-{{ $device->id }}" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-save"></i> Update Device</span>
                                    <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Saving...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        {{-- ═══════════════════ DOCS TAB ═══════════════════ --}}
        @if ($activeTab === 'docs')
            @include('crm.partials.helper-text', [
                'title' => 'Device Setup Guide',
                'content' => 'Step-by-step instructions for configuring each supported biometric device brand to push attendance events to this CRM.',
            ])

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">ZKTeco</p>
                        <h2>ZKTeco ADMS Setup (SpeedFace, ProFace, iClock, K-Series)</h2>
                    </div>
                </div>

                <div style="font-size: 13px; line-height: 1.7; color: #334155;">
                    <p><strong>Protocol:</strong> ADMS (Automatic Data Master Server) — the device pushes attendance data to your server over HTTP. No SDK installation needed.</p>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 1: Register the device in CRM</h4>
                    <ol style="padding-left: 20px;">
                        <li>Go to the <strong>Devices</strong> tab and click <strong>New Device</strong>.</li>
                        <li>Select <strong>ZKTeco</strong> as the brand and choose your model.</li>
                        <li>Enter the <strong>Device Identifier</strong> — use the device's serial number (found on the back label or in <em>Menu > System Info</em>).</li>
                        <li>Enter the device's <strong>IP address</strong> on your network and its <strong>port</strong> (default: 80).</li>
                        <li>Set <strong>Communication Key</strong> to match the value configured on the device (default: <code>0</code>).</li>
                        <li>Copy the <strong>API token</strong> shown after saving — you'll need it only if using the JSON endpoint instead of ADMS.</li>
                    </ol>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 2: Configure the device</h4>
                    <p>On the ZKTeco device, navigate to <strong>COMM. > Cloud Server Setting</strong> (or <strong>ADMS</strong> on older firmware):</p>
                    <div style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 3px; padding: 14px; margin: 10px 0; font-family: monospace; font-size: 12px;">
                        <strong>Enable Cloud Server:</strong> Yes<br>
                        <strong>Server Address:</strong> <code>{{ parse_url(url('/'), PHP_URL_HOST) }}</code><br>
                        <strong>Server Port:</strong> <code>{{ parse_url(url('/'), PHP_URL_PORT) ?: (request()->isSecure() ? '443' : '80') }}</code><br>
                        <strong>Enable Proxy Server:</strong> No<br>
                        <strong>Server Path:</strong> <code>/api/crm/attendance/iclock</code>
                    </div>
                    <p>The device will construct the full URL as: <code>{{ url('/api/crm/attendance/iclock/cdata') }}</code></p>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 3: Set communication parameters</h4>
                    <p>On the device under <strong>COMM. > Communication Key</strong>:</p>
                    <ul style="padding-left: 20px;">
                        <li><strong>Communication Key:</strong> Set to <code>0</code> (or match what you entered in CRM)</li>
                        <li><strong>Transfer Mode:</strong> Realtime + Fixed interval</li>
                        <li><strong>Transfer Interval:</strong> 30 seconds (or match CRM's Push Interval setting)</li>
                    </ul>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 4: Enrol employees on the device</h4>
                    <p>Each employee must be registered on the device with a <strong>User ID</strong> that matches their <strong>Personal Payroll Number</strong> in CRM:</p>
                    <ul style="padding-left: 20px;">
                        <li>On the device: <strong>User Mgt > New User</strong> — set the <strong>User ID</strong> (PIN) to the payroll number (e.g. <code>EMP-001</code>).</li>
                        <li>Register the employee's fingerprint, face, or card on the device.</li>
                        <li>In CRM: ensure the user's <strong>Personal Payroll Number</strong> field matches the device User ID exactly.</li>
                    </ul>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 5: Verify connection</h4>
                    <ul style="padding-left: 20px;">
                        <li>After saving, the device should appear as <strong>Online</strong> in the Devices tab within 1–2 minutes.</li>
                        <li>Ask an enrolled employee to clock in — the event should appear in <strong>Reports > Biometric Audit</strong>.</li>
                        <li>If the device shows offline, check: network connectivity, firewall rules (port 80/443), server URL, and communication key.</li>
                    </ul>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">ZKTeco Verify Type Codes</h4>
                    <div class="crm-table-wrap" style="margin-top: 8px;">
                        <table class="crm-table">
                            <thead><tr><th>Code</th><th>Method</th></tr></thead>
                            <tbody>
                                <tr><td>0</td><td>PIN / Password</td></tr>
                                <tr><td>1</td><td>Fingerprint</td></tr>
                                <tr><td>2</td><td>RFID Card</td></tr>
                                <tr><td>4</td><td>Card + Fingerprint</td></tr>
                                <tr><td>15</td><td>Face Recognition</td></tr>
                                <tr><td>20</td><td>Palm Print</td></tr>
                                <tr><td>21</td><td>Finger Vein</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">ADMS Data Format</h4>
                    <p>The device sends attendance logs as tab-delimited rows:</p>
                    <div style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 3px; padding: 14px; margin: 10px 0; font-family: monospace; font-size: 12px; overflow-x: auto;">
                        PIN &#9; Timestamp &#9; PunchState &#9; VerifyType &#9; WorkCode &#9; Reserved1 &#9; Reserved2 &#9; Reserved3<br><br>
                        <span style="color: #64748b;">Example:</span><br>
                        EMP-001 &#9; 2026-04-22 08:02:15 &#9; 0 &#9; 15 &#9; 0 &#9; 0 &#9; 0 &#9; 0
                    </div>
                    <p>Punch states: <code>0</code> = Clock In, <code>1</code> = Clock Out, <code>2</code> = Break Out, <code>3</code> = Break In</p>
                </div>
            </section>

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Hikvision</p>
                        <h2>Hikvision ISAPI Setup (DS-K1T, MinMoe Series)</h2>
                    </div>
                </div>

                <div style="font-size: 13px; line-height: 1.7; color: #334155;">
                    <p><strong>Protocol:</strong> ISAPI (Intelligent Security API) — the device pushes access control events as JSON/XML over HTTP to a configured listener.</p>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 1: Register the device in CRM</h4>
                    <ol style="padding-left: 20px;">
                        <li>Go to the <strong>Devices</strong> tab and click <strong>New Device</strong>.</li>
                        <li>Select <strong>Hikvision</strong> as the brand and choose your model.</li>
                        <li>Enter a unique <strong>Device Identifier</strong> (e.g. <code>HIK-ENTRANCE-01</code>).</li>
                        <li>Enter the device's <strong>IP address</strong> and <strong>port</strong> (default: 80).</li>
                        <li>Copy the <strong>API token</strong> shown after saving.</li>
                    </ol>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 2: Configure ISAPI push on the device</h4>
                    <p>Log into the device web interface at <code>http://&lt;device-ip&gt;</code> and navigate to <strong>Configuration > Network > Advanced > HTTP Listening</strong>:</p>
                    <div style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 3px; padding: 14px; margin: 10px 0; font-family: monospace; font-size: 12px;">
                        <strong>Listening Address:</strong> <code>{{ url('/api/crm/attendance/biometric-event') }}</code><br>
                        <strong>Listening Port:</strong> <code>{{ parse_url(url('/'), PHP_URL_PORT) ?: (request()->isSecure() ? '443' : '80') }}</code><br>
                        <strong>Protocol:</strong> HTTP{{ request()->isSecure() ? 'S' : '' }}<br>
                        <strong>Authentication:</strong> Bearer Token
                    </div>
                    <p>In the HTTP header, add: <code>Authorization: Bearer &lt;your-api-token&gt;</code></p>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 3: Configure event subscription</h4>
                    <p>Under <strong>Configuration > Event > Access Control Event</strong>:</p>
                    <ul style="padding-left: 20px;">
                        <li>Enable <strong>HTTP Listening</strong> notifications for access control events.</li>
                        <li>Ensure <strong>Attendance Status</strong> is enabled so the device sends <code>checkIn</code>/<code>checkOut</code> status.</li>
                        <li>Enable <strong>Capture on Event</strong> if you want face snapshots (optional).</li>
                    </ul>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 4: Enrol employees</h4>
                    <p>Via the device web interface under <strong>Configuration > Access Control > User Management</strong>:</p>
                    <ul style="padding-left: 20px;">
                        <li>Add each employee with their <strong>Employee No.</strong> matching their CRM <strong>Personal Payroll Number</strong>.</li>
                        <li>Register biometric credentials (face photo, fingerprint, or card number).</li>
                    </ul>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 5: Middleware adapter (recommended)</h4>
                    <p>Hikvision devices send events in ISAPI XML/JSON format. Since this CRM accepts a standard JSON payload, you may need a lightweight middleware script to transform the Hikvision payload.</p>
                    <p>The CRM's <code>BiometricEventProcessor</code> automatically normalises Hikvision payloads when the device brand is set to <code>hikvision</code>. The expected JSON structure:</p>
                    <div style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 3px; padding: 14px; margin: 10px 0; font-family: monospace; font-size: 12px;">
<pre style="margin: 0;">{
  "device_id": "HIK-ENTRANCE-01",
  "dateTime": "2026-04-22T08:15:33+02:00",
  "AccessControllerEvent": {
    "employeeNoString": "EMP-001",
    "currentVerifyMode": "face",
    "attendanceStatus": "checkIn",
    "cardNo": "",
    "currTemperature": 36.5
  }
}</pre>
                    </div>
                    <p>Supported <code>attendanceStatus</code> values: <code>checkIn</code>, <code>checkOut</code>, <code>breakOut</code>, <code>breakIn</code>, <code>overtimeIn</code>, <code>overtimeOut</code></p>
                    <p>Supported <code>currentVerifyMode</code> values: <code>fingerPrint</code>, <code>face</code>, <code>card</code>, <code>password</code>, <code>iris</code>, <code>palm</code></p>
                </div>
            </section>

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Suprema</p>
                        <h2>Suprema BioStar Setup (BioStation, FaceStation Series)</h2>
                    </div>
                </div>

                <div style="font-size: 13px; line-height: 1.7; color: #334155;">
                    <p><strong>Protocol:</strong> BioStar 2 API — events are forwarded from the BioStar 2 server to this CRM via webhook.</p>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 1: Register the device in CRM</h4>
                    <ol style="padding-left: 20px;">
                        <li>Select <strong>Suprema</strong> as the brand and choose your model.</li>
                        <li>Enter a unique <strong>Device Identifier</strong> and the device <strong>IP address</strong>.</li>
                        <li>Copy the generated <strong>API token</strong>.</li>
                    </ol>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 2: Configure BioStar 2 webhook</h4>
                    <p>In the BioStar 2 server admin console:</p>
                    <ol style="padding-left: 20px;">
                        <li>Go to <strong>Settings > Event > Webhook</strong>.</li>
                        <li>Add a new webhook with the URL: <code>{{ url('/api/crm/attendance/biometric-event') }}</code></li>
                        <li>Set the authentication header to: <code>Authorization: Bearer &lt;your-api-token&gt;</code></li>
                        <li>Subscribe to <strong>Access Control</strong> events (type: <code>device_access</code>).</li>
                    </ol>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 3: Payload mapping</h4>
                    <p>Configure BioStar's webhook to send a JSON payload in this format:</p>
                    <div style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 3px; padding: 14px; margin: 10px 0; font-family: monospace; font-size: 12px;">
<pre style="margin: 0;">{
  "device_id": "SUPREMA-ENTRANCE-01",
  "employee_identifier": "EMP-001",
  "event_type": "clock_in",
  "captured_at": "2026-04-22T08:15:33+02:00",
  "verification_method": "fingerprint",
  "confidence_score": 0.98
}</pre>
                    </div>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 4: Employee enrolment</h4>
                    <p>Ensure each user's <strong>User ID</strong> in BioStar 2 matches their <strong>Personal Payroll Number</strong> in CRM.</p>
                </div>
            </section>

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Anviz</p>
                        <h2>Anviz CrossChex Setup (FaceDeep, W2 Pro, EP Series)</h2>
                    </div>
                </div>

                <div style="font-size: 13px; line-height: 1.7; color: #334155;">
                    <p><strong>Protocol:</strong> CrossChex Cloud — events are forwarded from Anviz's CrossChex Cloud platform to this CRM via webhook.</p>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 1: Register the device in CRM</h4>
                    <ol style="padding-left: 20px;">
                        <li>Select <strong>Anviz</strong> as the brand and choose your model.</li>
                        <li>Enter the device's <strong>serial number</strong> as the Device Identifier.</li>
                        <li>Copy the generated <strong>API token</strong>.</li>
                    </ol>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 2: Configure CrossChex Cloud webhook</h4>
                    <ol style="padding-left: 20px;">
                        <li>Log into <a href="https://www.crosschexcloud.com" target="_blank" style="color: #2563eb;">CrossChex Cloud</a>.</li>
                        <li>Navigate to <strong>Settings > API > Webhooks</strong>.</li>
                        <li>Add a webhook URL: <code>{{ url('/api/crm/attendance/biometric-event') }}</code></li>
                        <li>Set the <code>Authorization</code> header to <code>Bearer &lt;your-api-token&gt;</code>.</li>
                        <li>Subscribe to <strong>Attendance Record</strong> events.</li>
                    </ol>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Step 3: Payload format</h4>
                    <p>Use the standard JSON push format:</p>
                    <div style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 3px; padding: 14px; margin: 10px 0; font-family: monospace; font-size: 12px;">
<pre style="margin: 0;">{
  "device_id": "ANVIZ-RECEPTION-01",
  "employee_identifier": "EMP-001",
  "event_type": "clock_in",
  "captured_at": "2026-04-22T08:15:33+02:00",
  "verification_method": "face"
}</pre>
                    </div>
                </div>
            </section>

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Generic / Custom</p>
                        <h2>Generic JSON Push API</h2>
                    </div>
                </div>

                <div style="font-size: 13px; line-height: 1.7; color: #334155;">
                    <p>For any device or system not listed above, use the standard JSON push endpoint.</p>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Endpoint</h4>
                    <div style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 3px; padding: 14px; margin: 10px 0; font-family: monospace; font-size: 12px;">
                        <strong>POST</strong> <code>{{ url('/api/crm/attendance/biometric-event') }}</code><br><br>
                        <strong>Headers:</strong><br>
                        Content-Type: application/json<br>
                        Accept: application/json<br>
                        Authorization: Bearer &lt;your-api-token&gt;
                    </div>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Request Body</h4>
                    <div style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 3px; padding: 14px; margin: 10px 0; font-family: monospace; font-size: 12px;">
<pre style="margin: 0;">{
  "device_id": "YOUR-DEVICE-ID",
  "employee_identifier": "EMP-001",
  "event_type": "clock_in",
  "captured_at": "2026-04-22T08:15:33+02:00",
  "verification_method": "fingerprint",
  "confidence_score": 0.95
}</pre>
                    </div>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Field Reference</h4>
                    <div class="crm-table-wrap" style="margin-top: 8px;">
                        <table class="crm-table">
                            <thead><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>device_id</code></td><td>string</td><td>Yes</td><td>Must match the Device Identifier registered in CRM</td></tr>
                                <tr><td><code>employee_identifier</code></td><td>string</td><td>Yes</td><td>Must match the user's Personal Payroll Number in CRM</td></tr>
                                <tr><td><code>event_type</code></td><td>string</td><td>Yes</td><td><code>clock_in</code> or <code>clock_out</code></td></tr>
                                <tr><td><code>captured_at</code></td><td>ISO 8601</td><td>Yes</td><td>When the biometric event occurred on the device</td></tr>
                                <tr><td><code>verification_method</code></td><td>string</td><td>No</td><td>fingerprint, face, card, pin, palm, iris, qr_code, bluetooth</td></tr>
                                <tr><td><code>confidence_score</code></td><td>decimal</td><td>No</td><td>0.00–1.00. Events below the device's min confidence are logged but not processed</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Response Codes</h4>
                    <div class="crm-table-wrap" style="margin-top: 8px;">
                        <table class="crm-table">
                            <thead><tr><th>Code</th><th>Meaning</th></tr></thead>
                            <tbody>
                                <tr><td><code>202 Accepted</code></td><td>Event queued for processing</td></tr>
                                <tr><td><code>401 Unauthorized</code></td><td>Missing or invalid API token</td></tr>
                                <tr><td><code>422 Unprocessable</code></td><td>Unknown device ID or validation error</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Heartbeat Endpoint</h4>
                    <div style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 3px; padding: 14px; margin: 10px 0; font-family: monospace; font-size: 12px;">
                        <strong>POST</strong> <code>{{ url('/api/crm/attendance/biometric-heartbeat') }}</code><br><br>
                        <strong>Body:</strong> <code>{{ '{"device_id": "YOUR-DEVICE-ID"}' }}</code>
                    </div>
                    <p>Send heartbeats every 30–60 seconds to keep the device status as <strong>Online</strong> in CRM.</p>

                    <h4 style="margin: 20px 0 10px; font-size: 14px;">Troubleshooting</h4>
                    <div class="crm-table-wrap" style="margin-top: 8px;">
                        <table class="crm-table">
                            <thead><tr><th>Issue</th><th>Cause</th><th>Solution</th></tr></thead>
                            <tbody>
                                <tr><td>Device shows Offline</td><td>No heartbeat received</td><td>Check network, firewall, and push server URL on the device</td></tr>
                                <tr><td>Events logged as "unmatched"</td><td>User ID on device doesn't match CRM payroll number</td><td>Verify the User ID/PIN on the device matches the Personal Payroll Number in CRM exactly</td></tr>
                                <tr><td>Events logged as "below_confidence"</td><td>Biometric match score too low</td><td>Re-enrol the user's biometric on the device, or lower the Min Confidence threshold</td></tr>
                                <tr><td>Events logged as "duplicate"</td><td>Same event received within 60 seconds</td><td>Normal — debounce prevents double-clocking. Adjust in config if needed</td></tr>
                                <tr><td>401 on API call</td><td>Invalid or expired Sanctum token</td><td>Regenerate the API token in Devices tab and update the device/middleware</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif
    </div>

    {{-- ═══════════════════ MODALS ═══════════════════ --}}

    {{-- Code Modal --}}
    <div class="modal fade" id="codeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="codeModalTitle">New Attendance Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="codeForm" action="{{ route('crm.settings.attendance.codes.store') }}" class="crm-form">
                        @csrf
                        <div id="codeFormMethod"></div>
                        <div class="crm-field-grid">
                            <div class="crm-field">
                                <label for="m-code">Code</label>
                                <input id="m-code" name="code" required maxlength="8" placeholder="e.g. WFH">
                            </div>
                            <div class="crm-field">
                                <label for="m-label">Label</label>
                                <input id="m-label" name="label" required maxlength="100" placeholder="e.g. Work From Home">
                            </div>
                            <div class="crm-field">
                                <label for="m-color">Color</label>
                                <input id="m-color" name="color" type="color" required value="#64748b">
                            </div>
                            <div class="crm-field">
                                <label for="m-category">Category</label>
                                <select id="m-category" name="category" required>
                                    <option value="presence">Presence</option>
                                    <option value="absence">Absence</option>
                                    <option value="leave">Leave</option>
                                    <option value="holiday">Holiday</option>
                                    <option value="duty">Duty</option>
                                </select>
                            </div>
                            <div class="crm-field">
                                <label for="m-working">Counts as Working (0–1)</label>
                                <input id="m-working" name="counts_as_working" type="number" step="0.01" min="0" max="1" required value="1.00" placeholder="e.g. 1.00">
                            </div>
                            <div class="crm-field">
                                <label for="m-sort">Sort Order</label>
                                <input id="m-sort" name="sort_order" type="number" min="0" value="0" placeholder="e.g. 10">
                            </div>
                        </div>
                        <div style="margin-top: 16px;">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" checked class="form-check-input" id="m-active">
                                <label class="form-check-label" for="m-active">Active</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light crm-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="codeForm" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Save Code</span>
                        <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Shift Modal --}}
    <div class="modal fade" id="shiftModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shiftModalTitle">New Shift Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="shiftForm" action="{{ route('crm.settings.attendance.shifts.store') }}" class="crm-form">
                        @csrf
                        <div id="shiftFormMethod"></div>

                        <div class="crm-field-grid">
                            <div class="crm-field"><label for="s-name">Name</label><input id="s-name" name="name" required maxlength="100" placeholder="e.g. Standard Office"></div>
                            <div class="crm-field"><label for="s-grace">Grace Minutes</label><input id="s-grace" name="grace_minutes" type="number" min="0" max="120" value="15" required placeholder="e.g. 15"></div>
                            <div class="crm-field"><label for="s-early">Early Out Minutes</label><input id="s-early" name="early_out_minutes" type="number" min="0" max="120" value="15" required placeholder="e.g. 15"></div>
                            <div class="crm-field"><label for="s-ot">Overtime After Minutes</label><input id="s-ot" name="overtime_after_minutes" type="number" min="0" max="240" value="30" required placeholder="e.g. 30"></div>
                            <div class="crm-field"><label for="s-earliest">Earliest Clock In</label><input id="s-earliest" name="earliest_clock_in" type="time" placeholder="e.g. 06:00"></div>
                            <div class="crm-field"><label for="s-latest">Latest Clock In</label><input id="s-latest" name="latest_clock_in" type="time" placeholder="e.g. 12:00"></div>
                        </div>

                        <p class="crm-kicker" style="margin: 20px 0 12px;">Weekly Schedule</p>
                        <div class="crm-table-wrap">
                            <table class="crm-table">
                                <thead><tr><th>Day</th><th>Start</th><th>End</th><th style="text-align: center;">Working</th></tr></thead>
                                <tbody>
                                    @foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $i => $dayName)
                                        <tr>
                                            <td><strong>{{ $dayName }}</strong></td>
                                            <td><input type="time" name="days[{{ $i }}][start_time]" id="s-day-{{ $i }}-start" value="08:00" required class="form-control form-control-sm" style="width: 130px;"></td>
                                            <td><input type="time" name="days[{{ $i }}][end_time]" id="s-day-{{ $i }}-end" value="17:00" required class="form-control form-control-sm" style="width: 130px;"></td>
                                            <td style="text-align: center;"><input type="checkbox" name="days[{{ $i }}][is_working_day]" id="s-day-{{ $i }}-working" value="1" class="form-check-input" {{ $i < 5 ? 'checked' : '' }}></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div style="display: flex; gap: 24px; margin-top: 16px;">
                            <div class="form-check">
                                <input type="checkbox" name="is_default" value="1" class="form-check-input" id="s-default">
                                <label class="form-check-label" for="s-default">Set as default</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="s-active" checked>
                                <label class="form-check-label" for="s-active">Active</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light crm-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="shiftForm" class="btn btn-primary btn-loading">
                        <span class="btn-text" id="shiftModalBtnText"><i class="fas fa-save"></i> Create Shift</span>
                        <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Holiday Modal --}}
    <div class="modal fade" id="holidayModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="holidayForm" action="{{ route('crm.settings.attendance.holidays.store') }}" class="crm-form">
                        @csrf
                        <div class="crm-field-grid">
                            <div class="crm-field"><label for="h-name">Name</label><input id="h-name" name="name" required maxlength="150" placeholder="e.g. Christmas Day"></div>
                            <div class="crm-field"><label for="h-date">Date</label><input id="h-date" name="date" type="date" required></div>
                            <div class="crm-field">
                                <label for="h-applies">Applies To</label>
                                <select id="h-applies" name="applies_to" required>
                                    <option value="all">All staff</option>
                                    <option value="department">Specific department</option>
                                    <option value="shift">Specific shift</option>
                                </select>
                            </div>
                            <div class="crm-field">
                                <label for="h-scope">Scope</label>
                                <select id="h-scope" name="scope_id">
                                    <option value="">— Not applicable —</option>
                                    <optgroup label="Departments">
                                        @foreach ($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Shifts">
                                        @foreach ($activeShifts as $shift)
                                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div style="display: flex; gap: 24px; margin-top: 16px;">
                            <div class="form-check">
                                <input type="checkbox" name="is_recurring" value="1" class="form-check-input" id="h-recurring">
                                <label class="form-check-label" for="h-recurring">Recurring (every year)</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="h-active" checked>
                                <label class="form-check-label" for="h-active">Active</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light crm-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="holidayForm" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Create Holiday</span>
                        <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Device Modal --}}
    <div class="modal fade" id="deviceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Register Biometric Device</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="deviceForm" action="{{ route('crm.settings.attendance.devices.store') }}" class="crm-form">
                        @csrf

                        <p class="crm-kicker" style="margin-bottom: 12px;">Device Identity</p>
                        <div class="crm-field-grid">
                            <div class="crm-field"><label for="d-name">Device Name</label><input id="d-name" name="name" required maxlength="100" placeholder="e.g. Main Entrance Scanner"></div>
                            <div class="crm-field">
                                <label for="d-brand">Brand</label>
                                <select id="d-brand" name="brand" required onchange="updateDeviceModels()">
                                    @foreach (config('heritage_crm.attendance.device_brands', []) as $key => $brandInfo)
                                        <option value="{{ $key }}">{{ $brandInfo['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="crm-field">
                                <label for="d-model">Model</label>
                                <select id="d-model" name="model">
                                    <option value="">— Select model —</option>
                                </select>
                            </div>
                            <div class="crm-field"><label for="d-identifier">Device Identifier</label><input id="d-identifier" name="device_identifier" required maxlength="50" placeholder="e.g. BIO-FRONT-01"></div>
                            <div class="crm-field"><label for="d-serial">Serial Number</label><input id="d-serial" name="serial_number" maxlength="80" placeholder="e.g. CGXH201360239"></div>
                            <div class="crm-field"><label for="d-location">Location</label><input id="d-location" name="location" maxlength="200" placeholder="e.g. Main building, ground floor"></div>
                        </div>

                        <p class="crm-kicker" style="margin: 20px 0 12px;">Network Configuration</p>
                        <div class="crm-field-grid">
                            <div class="crm-field"><label for="d-ip">IP Address</label><input id="d-ip" name="ip_address" maxlength="45" placeholder="e.g. 192.168.1.201"></div>
                            <div class="crm-field"><label for="d-port">Port</label><input id="d-port" name="port" type="number" min="1" max="65535" placeholder="e.g. 80"></div>
                            <div class="crm-field"><label for="d-commkey">Communication Key</label><input id="d-commkey" name="communication_key" maxlength="100" placeholder="e.g. 0 (default for ZKTeco)"></div>
                            <div class="crm-field">
                                <label for="d-direction">Direction</label>
                                <select id="d-direction" name="direction" required>
                                    @foreach (config('heritage_crm.attendance.device_directions', []) as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <p class="crm-kicker" style="margin: 20px 0 12px;">Timing & Thresholds</p>
                        <div class="crm-field-grid">
                            <div class="crm-field"><label for="d-timezone">Timezone Offset</label><input id="d-timezone" name="timezone" maxlength="40" placeholder="e.g. +2 or Africa/Gaborone"></div>
                            <div class="crm-field"><label for="d-heartbeat">Heartbeat Interval (seconds)</label><input id="d-heartbeat" name="heartbeat_interval" type="number" min="10" max="3600" value="60" placeholder="e.g. 60"></div>
                            <div class="crm-field"><label for="d-push">Push Interval (seconds)</label><input id="d-push" name="push_interval" type="number" min="5" max="3600" value="30" placeholder="e.g. 30"></div>
                            <div class="crm-field"><label for="d-confidence">Min Confidence (0–1)</label><input id="d-confidence" name="min_confidence" type="number" step="0.01" min="0" max="1" required value="0.80" placeholder="e.g. 0.80"></div>
                        </div>

                        <div style="margin-top: 16px;">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="d-active" checked>
                                <label class="form-check-label" for="d-active">Active</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light crm-btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="deviceForm" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Register Device</span>
                        <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function resetCodeModal() {
    var f = document.getElementById('codeForm');
    f.action = '{{ route("crm.settings.attendance.codes.store") }}';
    document.getElementById('codeFormMethod').innerHTML = '';
    document.getElementById('codeModalTitle').textContent = 'New Attendance Code';
    document.getElementById('m-code').value = '';
    document.getElementById('m-label').value = '';
    document.getElementById('m-color').value = '#64748b';
    document.getElementById('m-category').value = 'presence';
    document.getElementById('m-working').value = '1.00';
    document.getElementById('m-sort').value = '0';
    document.getElementById('m-active').checked = true;
}

function editCodeModal(id, code, label, color, category, working, active, sortOrder) {
    var f = document.getElementById('codeForm');
    f.action = '/crm/settings/attendance/codes/' + id;
    document.getElementById('codeFormMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('codeModalTitle').textContent = 'Edit Attendance Code';
    document.getElementById('m-code').value = code;
    document.getElementById('m-label').value = label;
    document.getElementById('m-color').value = color;
    document.getElementById('m-category').value = category;
    document.getElementById('m-working').value = working;
    document.getElementById('m-sort').value = sortOrder;
    document.getElementById('m-active').checked = active;
}

function resetShiftModal() {
    var f = document.getElementById('shiftForm');
    f.action = '{{ route("crm.settings.attendance.shifts.store") }}';
    document.getElementById('shiftFormMethod').innerHTML = '';
    document.getElementById('shiftModalTitle').textContent = 'New Shift Schedule';
    document.getElementById('shiftModalBtnText').innerHTML = '<i class="fas fa-save"></i> Create Shift';
    document.getElementById('s-name').value = '';
    document.getElementById('s-grace').value = '15';
    document.getElementById('s-early').value = '15';
    document.getElementById('s-ot').value = '30';
    document.getElementById('s-earliest').value = '';
    document.getElementById('s-latest').value = '';
    document.getElementById('s-default').checked = false;
    document.getElementById('s-active').checked = true;
    for (var i = 0; i < 7; i++) {
        document.getElementById('s-day-' + i + '-start').value = '08:00';
        document.getElementById('s-day-' + i + '-end').value = '17:00';
        document.getElementById('s-day-' + i + '-working').checked = i < 5;
    }
}

function editShiftModal(id, name, grace, early, ot, earliest, latest, isDefault, isActive, days) {
    var f = document.getElementById('shiftForm');
    f.action = '/crm/settings/attendance/shifts/' + id;
    document.getElementById('shiftFormMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('shiftModalTitle').textContent = 'Edit Shift Schedule';
    document.getElementById('shiftModalBtnText').innerHTML = '<i class="fas fa-save"></i> Update Shift';
    document.getElementById('s-name').value = name;
    document.getElementById('s-grace').value = grace;
    document.getElementById('s-early').value = early;
    document.getElementById('s-ot').value = ot;
    document.getElementById('s-earliest').value = earliest || '';
    document.getElementById('s-latest').value = latest || '';
    document.getElementById('s-default').checked = isDefault;
    document.getElementById('s-active').checked = isActive;
    for (var i = 0; i < 7; i++) {
        var day = days[i] || { start_time: '08:00', end_time: '17:00', is_working_day: false };
        document.getElementById('s-day-' + i + '-start').value = day.start_time;
        document.getElementById('s-day-' + i + '-end').value = day.end_time;
        document.getElementById('s-day-' + i + '-working').checked = day.is_working_day;
    }
}

// Reset shift modal when opening for new
document.addEventListener('DOMContentLoaded', function () {
    var shiftModal = document.getElementById('shiftModal');
    if (shiftModal) {
        shiftModal.addEventListener('hidden.bs.modal', function () {
            resetShiftModal();
        });
    }

    // Initialise device model dropdown
    updateDeviceModels();
});

var deviceBrandModels = @json(collect(config('heritage_crm.attendance.device_brands', []))->map(fn ($b) => $b['models'] ?? []));

function updateDeviceModels() {
    var brandSelect = document.getElementById('d-brand');
    var modelSelect = document.getElementById('d-model');
    if (!brandSelect || !modelSelect) return;

    var brand = brandSelect.value;
    var models = deviceBrandModels[brand] || {};

    modelSelect.innerHTML = '<option value="">— Select model —</option>';
    Object.keys(models).forEach(function (key) {
        var opt = document.createElement('option');
        opt.value = key;
        opt.textContent = models[key];
        modelSelect.appendChild(opt);
    });
}
</script>
@endpush

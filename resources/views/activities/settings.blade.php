@extends('layouts.master')

@section('title')
    Activities Settings
@endsection

@section('css')
    @include('activities.partials.theme')
    <style>
        .settings-shell {
            border: none;
            box-shadow: none;
        }

        .settings-shell .card-body {
            padding: 0;
        }

        .nav-tabs-custom {
            border-bottom: 1px solid #e5e7eb;
            gap: 8px;
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

        .settings-tab-body {
            padding: 24px;
        }

        .lookup-tabs {
            margin-bottom: 20px;
        }

        .lookup-tabs .nav-link {
            padding: 10px 16px;
            font-size: 14px;
        }

        .settings-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 18px;
        }

        .settings-toolbar .summary-card-title {
            margin-bottom: 4px;
        }

        .settings-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            background: #fff;
            overflow: hidden;
        }

        .settings-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 18px 20px;
            border-bottom: 1px solid #e5e7eb;
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.95), rgba(255, 255, 255, 0.95));
        }

        .settings-card-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }

        .settings-card-subtitle {
            margin: 6px 0 0;
            font-size: 13px;
            color: #6b7280;
        }

        .settings-card-body {
            padding: 20px;
        }

        .settings-table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            white-space: nowrap;
        }

        .settings-table tbody tr:hover {
            background: #f9fafb;
        }

        .settings-table td {
            vertical-align: middle;
        }

        .settings-table .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .settings-table .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .notes-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .settings-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 999px;
        }

        .settings-badge-system {
            color: #1d4ed8;
            background: rgba(37, 99, 235, 0.12);
        }

        .settings-badge-behavior {
            color: #92400e;
            background: rgba(245, 158, 11, 0.16);
        }

        .settings-footer-note {
            margin-top: 14px;
            font-size: 13px;
            color: #6b7280;
        }

        .empty-table-state {
            text-align: center;
            color: #6b7280;
            padding: 36px 0;
        }

        .empty-table-state i {
            font-size: 32px;
            opacity: 0.35;
        }

        .group-error-note {
            margin-bottom: 16px;
        }

        .defaults-section + .defaults-section {
            margin-top: 28px;
        }

        .defaults-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .option-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-top: 16px;
        }

        .option-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 14px 16px;
            background: #f9fafb;
        }

        .option-card .form-check {
            margin: 0;
        }

        .option-card .form-check-label {
            color: #374151;
            font-weight: 500;
        }

        .option-card .option-help {
            display: block;
            margin-top: 6px;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.4;
        }

        .modal-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            border-radius: 3px 3px 0 0;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-title {
            font-weight: 600;
        }

        .modal-key-note {
            margin-top: 6px;
            font-size: 12px;
            color: #6b7280;
        }

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

        @media (max-width: 767.98px) {
            .settings-tab-body {
                padding: 18px;
            }

            .settings-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .option-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $tabHelp = [
            'title' => 'Activities Settings',
            'content' => 'Manage lookup taxonomies for activities and events, and choose the values that prefill new records. Changes are saved together.',
        ];

        $groupMeta = [
            'categories' => [
                'icon' => 'fas fa-layer-group',
                'description' => 'Use categories to group activities in the catalog, filters, and reports.',
                'singular' => 'Category',
                'empty' => 'No categories configured yet.',
            ],
            'delivery_modes' => [
                'icon' => 'fas fa-repeat',
                'description' => 'Delivery modes describe whether an activity runs once, repeatedly, or in a hybrid format.',
                'singular' => 'Delivery Mode',
                'empty' => 'No delivery modes configured yet.',
            ],
            'participation_modes' => [
                'icon' => 'fas fa-users',
                'description' => 'Participation modes define whether activity enrollment and results are individual, team-based, or mixed.',
                'singular' => 'Participation Mode',
                'empty' => 'No participation modes configured yet.',
            ],
            'result_modes' => [
                'icon' => 'fas fa-trophy',
                'description' => 'Result modes control the style of output available for events and result entry.',
                'singular' => 'Result Mode',
                'empty' => 'No result modes configured yet.',
            ],
            'gender_policies' => [
                'icon' => 'fas fa-people-group',
                'description' => 'Gender policies are used on the activity form and remain visible on historical records after deactivation.',
                'singular' => 'Gender Policy',
                'empty' => 'No gender policies configured yet.',
            ],
            'event_types' => [
                'icon' => 'fas fa-calendar-day',
                'description' => 'Event types classify fixtures, showcases, competitions, and other event records.',
                'singular' => 'Event Type',
                'empty' => 'No event types configured yet.',
            ],
        ];

        $currentFieldGroups = array_merge($activityFieldGroups, $eventFieldGroups);

        $defaultsKey = '__defaults';
        $availableGroupKeys = array_merge(array_keys($currentFieldGroups), [$defaultsKey]);
        $requestedGroup = old('group', request('group'));
        $activeGroup = in_array($requestedGroup, $availableGroupKeys, true) ? $requestedGroup : ($availableGroupKeys[0] ?? null);
        $groupLabels = collect($groupMeta)->mapWithKeys(fn (array $meta, string $key) => [$key => $meta['singular']])->all();
    @endphp

    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('activities.index') }}">Activities</a>
        @endslot
        @slot('title')
            Activities Settings
        @endslot
    @endcomponent

    @include('activities.partials.alerts')

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Activities Settings</h1>
                <p class="page-subtitle">Configure module lookups and defaults without touching workflow statuses or lifecycle controls.</p>
            </div>
        </div>

        <div class="help-text">
            <div class="help-title">{{ $tabHelp['title'] }}</div>
            <div class="help-content">{{ $tabHelp['content'] }}</div>
        </div>

        <div class="card-shell settings-shell">
            <div class="card-body">
                <div class="settings-tab-body">
                    <form action="{{ route('activities.settings.update') }}"
                        method="POST"
                        class="needs-validation"
                        novalidate
                        data-activity-form>
                        @csrf
                        <input type="hidden" name="tab" value="{{ \App\Services\Activities\ActivitySettingsService::TAB_ALL }}">
                        <input type="hidden" name="group" value="{{ $activeGroup }}" id="settings-group-input">

                        <ul class="nav nav-tabs nav-tabs-custom lookup-tabs d-flex justify-content-start flex-wrap" role="tablist">
                            @foreach ($currentFieldGroups as $groupKey => $group)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $activeGroup === $groupKey ? 'active' : '' }}"
                                        id="lookup-tab-{{ $groupKey }}"
                                        data-bs-toggle="tab"
                                        data-bs-target="#lookup-pane-{{ $groupKey }}"
                                        type="button"
                                        role="tab"
                                        aria-controls="lookup-pane-{{ $groupKey }}"
                                        aria-selected="{{ $activeGroup === $groupKey ? 'true' : 'false' }}"
                                        data-group-tab="{{ $groupKey }}">
                                        <i class="{{ $groupMeta[$groupKey]['icon'] ?? 'fas fa-list' }} me-2 text-muted"></i>{{ $group['label'] }}
                                    </button>
                                </li>
                            @endforeach
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $activeGroup === $defaultsKey ? 'active' : '' }}"
                                    id="lookup-tab-{{ $defaultsKey }}"
                                    data-bs-toggle="tab"
                                    data-bs-target="#lookup-pane-{{ $defaultsKey }}"
                                    type="button"
                                    role="tab"
                                    aria-controls="lookup-pane-{{ $defaultsKey }}"
                                    aria-selected="{{ $activeGroup === $defaultsKey ? 'true' : 'false' }}"
                                    data-group-tab="{{ $defaultsKey }}">
                                    <i class="bx bx-slider-alt me-2 text-muted"></i>Defaults
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-3">
                            @foreach ($currentFieldGroups as $groupKey => $group)
                                    @php
                                        $submittedRows = old($groupKey);
                                        $rows = is_array($submittedRows) ? $submittedRows : $group['rows'];
                                        $groupHasErrors =
                                            count($errors->get($groupKey)) > 0 ||
                                            count($errors->get($groupKey . '.*.key')) > 0 ||
                                            count($errors->get($groupKey . '.*.label')) > 0 ||
                                            count($errors->get($groupKey . '.*.active')) > 0;
                                    @endphp
                                    <div class="tab-pane fade {{ $activeGroup === $groupKey ? 'show active' : '' }}"
                                        id="lookup-pane-{{ $groupKey }}"
                                        role="tabpanel"
                                        aria-labelledby="lookup-tab-{{ $groupKey }}">
                                        <div class="settings-toolbar">
                                            <div>
                                                <h5 class="summary-card-title">{{ $group['label'] }}</h5>
                                                <p class="management-subtitle mb-0">{{ $groupMeta[$groupKey]['description'] ?? 'Manage the options available for this lookup list.' }}</p>
                                            </div>
                                            <button type="button"
                                                class="btn btn-primary"
                                                data-open-create-modal
                                                data-group="{{ $groupKey }}">
                                                <i class="fas fa-plus me-1"></i> New {{ $groupMeta[$groupKey]['singular'] ?? 'Option' }}
                                            </button>
                                        </div>

                                        @if ($groupHasErrors)
                                            <div class="alert alert-danger group-error-note mb-3" role="alert">
                                                Review the highlighted rows in {{ strtolower($group['label']) }} before saving.
                                            </div>
                                        @endif

                                        <div class="table-responsive">
                                            <table class="table table-striped align-middle settings-table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>{{ $groupMeta[$groupKey]['singular'] ?? 'Option' }}</th>
                                                        <th>Key</th>
                                                        <th>Status</th>
                                                        <th>Notes</th>
                                                        <th class="text-end">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody data-settings-list data-group="{{ $groupKey }}">
                                                    @foreach ($rows as $index => $row)
                                                        @php
                                                            $row = array_merge([
                                                                'key' => '',
                                                                'label' => '',
                                                                'active' => true,
                                                                'system' => false,
                                                                'allows_results' => true,
                                                            ], is_array($row) ? $row : []);

                                                            $rowHasErrors =
                                                                count($errors->get($groupKey . '.' . $index . '.key')) > 0 ||
                                                                count($errors->get($groupKey . '.' . $index . '.label')) > 0 ||
                                                                count($errors->get($groupKey . '.' . $index . '.active')) > 0;
                                                        @endphp
                                                        <tr data-settings-row
                                                            data-group="{{ $groupKey }}"
                                                            data-index="{{ $index }}"
                                                            data-error="{{ $rowHasErrors ? '1' : '0' }}"
                                                            data-system="{{ !empty($row['system']) ? '1' : '0' }}"
                                                            data-allows-results="{{ $groupKey === 'result_modes' && array_key_exists('allows_results', $row) && $row['allows_results'] === false ? '0' : '1' }}"
                                                            class="{{ $rowHasErrors ? 'table-danger' : '' }}">
                                                            <td class="fw-semibold" data-cell="label">{{ $row['label'] }}</td>
                                                            <td class="text-muted small font-monospace" data-cell="key">{{ $row['key'] }}</td>
                                                            <td data-cell="status">
                                                                <span class="status-badge {{ !empty($row['active']) ? 'status-active' : 'status-inactive' }}">
                                                                    {{ !empty($row['active']) ? 'Active' : 'Inactive' }}
                                                                </span>
                                                            </td>
                                                            <td data-cell="notes">
                                                                <div class="notes-stack">
                                                                    @if (!empty($row['system']))
                                                                        <span class="settings-badge settings-badge-system">
                                                                            <i class="bx bx-lock-alt"></i> System Seed
                                                                        </span>
                                                                    @endif
                                                                    @if ($groupKey === 'result_modes' && array_key_exists('allows_results', $row) && $row['allows_results'] === false)
                                                                        <span class="settings-badge settings-badge-behavior">
                                                                            Results entry disabled
                                                                        </span>
                                                                    @endif
                                                                    @if (empty($row['system']) && !($groupKey === 'result_modes' && array_key_exists('allows_results', $row) && $row['allows_results'] === false))
                                                                        <span class="text-muted small">Custom option</span>
                                                                    @endif
                                                                    @if ($rowHasErrors)
                                                                        <span class="text-danger small">Needs attention</span>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                            <td class="text-end">
                                                                <div class="action-buttons">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-info"
                                                                        data-open-edit-modal
                                                                        title="Edit {{ $groupMeta[$groupKey]['singular'] ?? 'Option' }}">
                                                                        <i class="bx bx-edit-alt"></i>
                                                                    </button>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-secondary"
                                                                        data-move-up
                                                                        title="Move Up">
                                                                        <i class="bx bx-chevron-up"></i>
                                                                    </button>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-secondary"
                                                                        data-move-down
                                                                        title="Move Down">
                                                                        <i class="bx bx-chevron-down"></i>
                                                                    </button>
                                                                </div>
                                                                <input type="hidden" data-field="key" name="{{ $groupKey }}[{{ $index }}][key]" value="{{ $row['key'] }}">
                                                                <input type="hidden" data-field="label" name="{{ $groupKey }}[{{ $index }}][label]" value="{{ $row['label'] }}">
                                                                <input type="hidden" data-field="active" name="{{ $groupKey }}[{{ $index }}][active]" value="{{ !empty($row['active']) ? 1 : 0 }}">
                                                                <input type="hidden" data-field="system" name="{{ $groupKey }}[{{ $index }}][system]" value="{{ !empty($row['system']) ? 1 : 0 }}">
                                                                @if ($groupKey === 'result_modes')
                                                                    <input type="hidden" data-meta="allows-results" value="{{ array_key_exists('allows_results', $row) && $row['allows_results'] === false ? 0 : 1 }}">
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    <tr data-empty-row class="{{ count($rows) > 0 ? 'd-none' : '' }}">
                                                        <td colspan="5">
                                                            <div class="empty-table-state">
                                                                <i class="{{ $groupMeta[$groupKey]['icon'] ?? 'fas fa-list' }}"></i>
                                                                <p class="mt-2 mb-0">{{ $groupMeta[$groupKey]['empty'] ?? 'No options configured yet.' }}</p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="settings-footer-note">
                                            Inactive items disappear from new forms, but existing activities, events, reports, and audit history still resolve their saved labels.
                                        </div>
                                    </div>
                                @endforeach

                                <div class="tab-pane fade {{ $activeGroup === $defaultsKey ? 'show active' : '' }}"
                                    id="lookup-pane-{{ $defaultsKey }}"
                                    role="tabpanel"
                                    aria-labelledby="lookup-tab-{{ $defaultsKey }}">
                                    <div class="settings-card defaults-section">
                                        <div class="settings-card-header">
                                            <div>
                                                <h2 class="settings-card-title">New Activity Defaults</h2>
                                                <p class="settings-card-subtitle">These values prefill the activity create form and can still be changed per record.</p>
                                            </div>
                                        </div>
                                        <div class="settings-card-body">
                                            <div class="defaults-grid">
                                                <div class="form-group">
                                                    <label class="form-label" for="default_category">Category</label>
                                                    <select class="form-select @error('default_category') is-invalid @enderror" id="default_category" name="default_category">
                                                        @foreach ($activeCategoryOptions as $key => $label)
                                                            <option value="{{ $key }}" {{ old('default_category', $activityDefaults['category']) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('default_category')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label" for="default_delivery_mode">Delivery Mode</label>
                                                    <select class="form-select @error('default_delivery_mode') is-invalid @enderror" id="default_delivery_mode" name="default_delivery_mode">
                                                        @foreach ($activeDeliveryModeOptions as $key => $label)
                                                            <option value="{{ $key }}" {{ old('default_delivery_mode', $activityDefaults['delivery_mode']) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('default_delivery_mode')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label" for="default_participation_mode">Participation Mode</label>
                                                    <select class="form-select @error('default_participation_mode') is-invalid @enderror" id="default_participation_mode" name="default_participation_mode">
                                                        @foreach ($activeParticipationModeOptions as $key => $label)
                                                            <option value="{{ $key }}" {{ old('default_participation_mode', $activityDefaults['participation_mode']) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('default_participation_mode')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label" for="default_result_mode">Result Mode</label>
                                                    <select class="form-select @error('default_result_mode') is-invalid @enderror" id="default_result_mode" name="default_result_mode">
                                                        @foreach ($activeResultModeOptions as $key => $label)
                                                            <option value="{{ $key }}" {{ old('default_result_mode', $activityDefaults['result_mode']) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('default_result_mode')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label" for="default_gender_policy">Gender Policy</label>
                                                    <select class="form-select @error('default_gender_policy') is-invalid @enderror" id="default_gender_policy" name="default_gender_policy">
                                                        @foreach ($activeGenderPolicyOptions as $key => $label)
                                                            <option value="{{ $key }}" {{ old('default_gender_policy', $activityDefaults['gender_policy']) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('default_gender_policy')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label class="form-label" for="default_capacity">Capacity</label>
                                                    <input type="number"
                                                        min="1"
                                                        class="form-control @error('default_capacity') is-invalid @enderror"
                                                        id="default_capacity"
                                                        name="default_capacity"
                                                        value="{{ old('default_capacity', $activityDefaults['capacity']) }}"
                                                        placeholder="Optional default capacity">
                                                    @error('default_capacity')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="option-grid mt-4">
                                                <div class="option-card">
                                                    <div class="form-check">
                                                        <input type="hidden" name="default_attendance_required" value="0">
                                                        <input class="form-check-input" type="checkbox" id="default_attendance_required" name="default_attendance_required" value="1" {{ old('default_attendance_required', $activityDefaults['attendance_required']) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="default_attendance_required">Attendance required by default</label>
                                                        <span class="option-help">New activities start with attendance tracking switched on.</span>
                                                    </div>
                                                </div>
                                                <div class="option-card">
                                                    <div class="form-check">
                                                        <input type="hidden" name="default_allow_house_linkage" value="0">
                                                        <input class="form-check-input" type="checkbox" id="default_allow_house_linkage" name="default_allow_house_linkage" value="1" {{ old('default_allow_house_linkage', $activityDefaults['allow_house_linkage']) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="default_allow_house_linkage">Allow house linkage by default</label>
                                                        <span class="option-help">New activities can start ready for house-linked event reporting.</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="settings-card defaults-section">
                                        <div class="settings-card-header">
                                            <div>
                                                <h2 class="settings-card-title">New Event Defaults</h2>
                                                <p class="settings-card-subtitle">These values prefill the event create form. House-linked events still respect the activity-level house linkage setting.</p>
                                            </div>
                                        </div>
                                        <div class="settings-card-body">
                                            <div class="defaults-grid">
                                                <div class="form-group">
                                                    <label class="form-label" for="default_event_type">Event Type</label>
                                                    <select class="form-select @error('default_event_type') is-invalid @enderror" id="default_event_type" name="default_event_type">
                                                        @foreach ($activeEventTypeOptions as $key => $label)
                                                            <option value="{{ $key }}" {{ old('default_event_type', $eventDefaults['event_type']) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('default_event_type')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="option-grid mt-4">
                                                <div class="option-card">
                                                    <div class="form-check">
                                                        <input type="hidden" name="default_publish_to_calendar" value="0">
                                                        <input class="form-check-input" type="checkbox" id="default_publish_to_calendar" name="default_publish_to_calendar" value="1" {{ old('default_publish_to_calendar', $eventDefaults['publish_to_calendar']) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="default_publish_to_calendar">Hold for calendar output by default</label>
                                                        <span class="option-help">New events keep the publication intent flag on unless staff switch it off.</span>
                                                    </div>
                                                </div>
                                                <div class="option-card">
                                                    <div class="form-check">
                                                        <input type="hidden" name="default_house_linked" value="0">
                                                        <input class="form-check-input" type="checkbox" id="default_house_linked" name="default_house_linked" value="1" {{ old('default_house_linked', $eventDefaults['house_linked']) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="default_house_linked">Default to house-linked events</label>
                                                        <span class="option-help">This only applies when the underlying activity already allows house-linked reporting.</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions mt-4">
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-save"></i> Save Settings</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($currentFieldGroups)
        <div class="modal fade" id="optionModal" tabindex="-1" aria-labelledby="optionModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="optionModalForm" novalidate>
                        <div class="modal-header">
                            <h5 class="modal-title" id="optionModalLabel">New Option</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="modal_group_key">
                            <input type="hidden" id="modal_system" value="0">
                            <input type="hidden" id="modal_allows_results" value="1">

                            <div class="mb-3">
                                <label class="form-label" for="modal_option_label">Label</label>
                                <input type="text"
                                    class="form-control"
                                    id="modal_option_label"
                                    placeholder="Display label"
                                    maxlength="120"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="modal_option_key">Key</label>
                                <input type="text"
                                    class="form-control"
                                    id="modal_option_key"
                                    placeholder="auto_generated_key"
                                    maxlength="50"
                                    pattern="[A-Za-z0-9_-]+"
                                    required>
                                <div class="modal-key-note" id="modal_key_note">
                                    Letters, numbers, dashes, and underscores only.
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label" for="modal_option_active">State</label>
                                <select class="form-select" id="modal_option_active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="optionModalSaveButton">
                                <i class="fas fa-save"></i> <span id="optionModalSubmitLabel">Add Option</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('script')
    @include('activities.partials.form-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const groupLabels = @json($groupLabels);
            const optionModalElement = document.getElementById('optionModal');
            const groupInput = document.getElementById('settings-group-input');

            document.querySelectorAll('[data-settings-list]').forEach(function(list) {
                reindexList(list);
                renderList(list);
            });

            document.querySelectorAll('[data-group-tab]').forEach(function(tabButton) {
                tabButton.addEventListener('shown.bs.tab', function(event) {
                    if (groupInput) {
                        groupInput.value = event.target.dataset.groupTab;
                    }
                });
            });

            if (!optionModalElement || typeof bootstrap === 'undefined') {
                return;
            }

            const optionModal = new bootstrap.Modal(optionModalElement);
            const optionModalForm = document.getElementById('optionModalForm');
            const modalTitle = document.getElementById('optionModalLabel');
            const modalSubmitLabel = document.getElementById('optionModalSubmitLabel');
            const modalGroup = document.getElementById('modal_group_key');
            const modalSystem = document.getElementById('modal_system');
            const modalAllowsResults = document.getElementById('modal_allows_results');
            const modalLabel = document.getElementById('modal_option_label');
            const modalKey = document.getElementById('modal_option_key');
            const modalActive = document.getElementById('modal_option_active');
            const modalKeyNote = document.getElementById('modal_key_note');
            const modalSaveButton = document.getElementById('optionModalSaveButton');

            let currentRow = null;

            function slugify(value) {
                return value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/g, '_')
                    .replace(/^_+|_+$/g, '');
            }

            function getList(group) {
                return document.querySelector('[data-settings-list][data-group="' + group + '"]');
            }

            function renderStatus(active) {
                return '<span class="status-badge ' + (active ? 'status-active' : 'status-inactive') + '">' + (active ? 'Active' : 'Inactive') + '</span>';
            }

            function renderNotes(row) {
                const notes = [];
                const system = row.dataset.system === '1';
                const allowsResults = row.dataset.allowsResults !== '0';

                if (system) {
                    notes.push('<span class="settings-badge settings-badge-system"><i class="bx bx-lock-alt"></i> System Seed</span>');
                }

                if (!allowsResults) {
                    notes.push('<span class="settings-badge settings-badge-behavior">Results entry disabled</span>');
                }

                if (!system && allowsResults) {
                    notes.push('<span class="text-muted small">Custom option</span>');
                }

                if (row.dataset.error === '1') {
                    notes.push('<span class="text-danger small">Needs attention</span>');
                }

                return '<div class="notes-stack">' + notes.join('') + '</div>';
            }

            function renderRow(row) {
                const label = row.querySelector('[data-field="label"]').value.trim();
                const key = row.querySelector('[data-field="key"]').value.trim();
                const active = row.querySelector('[data-field="active"]').value === '1';
                const system = row.querySelector('[data-field="system"]').value === '1';
                const allowsResultsInput = row.querySelector('[data-meta="allows-results"]');

                row.dataset.system = system ? '1' : '0';
                row.dataset.allowsResults = allowsResultsInput && allowsResultsInput.value === '0' ? '0' : '1';

                row.querySelector('[data-cell="label"]').textContent = label || '—';
                row.querySelector('[data-cell="key"]').textContent = key || '—';
                row.querySelector('[data-cell="status"]').innerHTML = renderStatus(active);
                row.querySelector('[data-cell="notes"]').innerHTML = renderNotes(row);
            }

            function reindexList(list) {
                const group = list.dataset.group;

                list.querySelectorAll('[data-settings-row]').forEach(function(row, index) {
                    row.dataset.index = String(index);

                    row.querySelectorAll('[data-field]').forEach(function(input) {
                        input.name = group + '[' + index + '][' + input.dataset.field + ']';
                    });
                });
            }

            function syncMoveButtons(list) {
                const rows = Array.from(list.querySelectorAll('[data-settings-row]'));

                rows.forEach(function(row, index) {
                    const moveUpButton = row.querySelector('[data-move-up]');
                    const moveDownButton = row.querySelector('[data-move-down]');

                    if (moveUpButton) {
                        moveUpButton.disabled = index === 0;
                    }

                    if (moveDownButton) {
                        moveDownButton.disabled = index === rows.length - 1;
                    }
                });
            }

            function syncEmptyState(list) {
                const emptyRow = list.querySelector('[data-empty-row]');
                if (!emptyRow) {
                    return;
                }

                emptyRow.classList.toggle('d-none', list.querySelectorAll('[data-settings-row]').length > 0);
            }

            function renderList(list) {
                list.querySelectorAll('[data-settings-row]').forEach(renderRow);
                syncMoveButtons(list);
                syncEmptyState(list);
            }

            function createRow(group, data) {
                const row = document.createElement('tr');
                row.dataset.settingsRow = 'true';
                row.dataset.group = group;
                row.dataset.error = '0';

                row.innerHTML = `
                    <td class="fw-semibold" data-cell="label"></td>
                    <td class="text-muted small font-monospace" data-cell="key"></td>
                    <td data-cell="status"></td>
                    <td data-cell="notes"></td>
                    <td class="text-end">
                        <div class="action-buttons">
                            <button type="button" class="btn btn-sm btn-outline-info" data-open-edit-modal title="Edit">
                                <i class="bx bx-edit-alt"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-move-up title="Move Up">
                                <i class="bx bx-chevron-up"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-move-down title="Move Down">
                                <i class="bx bx-chevron-down"></i>
                            </button>
                        </div>
                        <input type="hidden" data-field="key">
                        <input type="hidden" data-field="label">
                        <input type="hidden" data-field="active">
                        <input type="hidden" data-field="system">
                        ${group === 'result_modes' ? '<input type="hidden" data-meta="allows-results">' : ''}
                    </td>
                `;

                row.querySelector('[data-field="key"]').value = data.key;
                row.querySelector('[data-field="label"]').value = data.label;
                row.querySelector('[data-field="active"]').value = data.active;
                row.querySelector('[data-field="system"]').value = data.system;

                if (group === 'result_modes') {
                    row.querySelector('[data-meta="allows-results"]').value = data.allowsResults;
                }

                renderRow(row);

                return row;
            }

            function setModalMode(group, mode, row) {
                currentRow = row || null;
                modalGroup.value = group;
                modalSystem.value = row ? row.querySelector('[data-field="system"]').value : '0';
                modalAllowsResults.value = row && row.querySelector('[data-meta="allows-results"]')
                    ? row.querySelector('[data-meta="allows-results"]').value
                    : '1';

                modalTitle.textContent = (mode === 'create' ? 'New ' : 'Edit ') + (groupLabels[group] || 'Option');
                modalSubmitLabel.textContent = mode === 'create'
                    ? 'Add ' + (groupLabels[group] || 'Option')
                    : 'Save Changes';

                modalLabel.value = row ? row.querySelector('[data-field="label"]').value : '';
                modalKey.value = row ? row.querySelector('[data-field="key"]').value : '';
                modalActive.value = row ? row.querySelector('[data-field="active"]').value : '1';
                modalKey.dataset.autoKey = mode === 'create' ? '1' : '0';

                const isSystem = modalSystem.value === '1';
                modalKey.readOnly = isSystem;
                modalKeyNote.textContent = isSystem
                    ? 'System seeded keys stay locked. You can update the label or deactivate the option.'
                    : 'Letters, numbers, dashes, and underscores only.';

                modalKey.setCustomValidity('');
                optionModalForm.classList.remove('was-validated');
            }

            function keyExists(group, key, ignoreRow) {
                const list = getList(group);
                if (!list) {
                    return false;
                }

                return Array.from(list.querySelectorAll('[data-settings-row]')).some(function(row) {
                    if (ignoreRow && row === ignoreRow) {
                        return false;
                    }

                    return row.querySelector('[data-field="key"]').value === key;
                });
            }

            modalLabel.addEventListener('input', function() {
                if (modalKey.dataset.autoKey === '1' && !modalKey.readOnly) {
                    modalKey.value = slugify(modalLabel.value);
                }
            });

            modalKey.addEventListener('input', function() {
                modalKey.dataset.autoKey = '0';
                modalKey.setCustomValidity('');
            });

            modalSaveButton.addEventListener('click', function() {
                const group = modalGroup.value;
                const key = (modalKey.value.trim() || slugify(modalLabel.value)).trim();
                const label = modalLabel.value.trim();

                modalKey.value = key;

                if (keyExists(group, key, currentRow)) {
                    modalKey.setCustomValidity('Duplicate key.');
                } else {
                    modalKey.setCustomValidity('');
                }

                if (!optionModalForm.checkValidity()) {
                    optionModalForm.classList.add('was-validated');
                    optionModalForm.reportValidity();
                    return;
                }

                const list = getList(group);
                if (!list) {
                    return;
                }

                const rowData = {
                    key: key,
                    label: label,
                    active: modalActive.value,
                    system: modalSystem.value,
                    allowsResults: modalAllowsResults.value || '1',
                };

                let row = currentRow;

                if (!row) {
                    row = createRow(group, rowData);
                    list.appendChild(row);
                } else {
                    row.dataset.error = '0';
                    row.classList.remove('table-danger');
                    row.querySelector('[data-field="key"]').value = rowData.key;
                    row.querySelector('[data-field="label"]').value = rowData.label;
                    row.querySelector('[data-field="active"]').value = rowData.active;
                    row.querySelector('[data-field="system"]').value = rowData.system;

                    const allowsResultsInput = row.querySelector('[data-meta="allows-results"]');
                    if (allowsResultsInput) {
                        allowsResultsInput.value = rowData.allowsResults;
                    }

                    renderRow(row);
                }

                reindexList(list);
                renderList(list);
                optionModal.hide();
            });

            optionModalElement.addEventListener('hidden.bs.modal', function() {
                currentRow = null;
                optionModalForm.reset();
                optionModalForm.classList.remove('was-validated');
                modalKey.readOnly = false;
                modalKey.setCustomValidity('');
            });

            document.addEventListener('click', function(event) {
                const createButton = event.target.closest('[data-open-create-modal]');
                if (createButton) {
                    const group = createButton.dataset.group;
                    if (groupInput) {
                        groupInput.value = group;
                    }
                    setModalMode(group, 'create');
                    optionModal.show();
                    return;
                }

                const editButton = event.target.closest('[data-open-edit-modal]');
                if (editButton) {
                    const row = editButton.closest('[data-settings-row]');
                    if (!row) {
                        return;
                    }
                    if (groupInput) {
                        groupInput.value = row.dataset.group;
                    }
                    setModalMode(row.dataset.group, 'edit', row);
                    optionModal.show();
                    return;
                }

                const moveUpButton = event.target.closest('[data-move-up]');
                if (moveUpButton) {
                    const row = moveUpButton.closest('[data-settings-row]');
                    const previous = row ? row.previousElementSibling : null;

                    if (row && previous && previous.hasAttribute('data-settings-row')) {
                        row.parentNode.insertBefore(row, previous);
                        reindexList(row.parentNode);
                        renderList(row.parentNode);
                    }

                    return;
                }

                const moveDownButton = event.target.closest('[data-move-down]');
                if (moveDownButton) {
                    const row = moveDownButton.closest('[data-settings-row]');
                    const next = row ? row.nextElementSibling : null;

                    if (row && next && next.hasAttribute('data-settings-row')) {
                        row.parentNode.insertBefore(next, row);
                        reindexList(row.parentNode);
                        renderList(row.parentNode);
                    }
                }
            });
        });
    </script>
@endsection

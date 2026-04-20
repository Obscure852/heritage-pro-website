@extends('layouts.master')

@section('title', 'PDP Settings')
@section('page_title', 'PDP Settings')
@section('css')
    @include('pdp.partials.theme-css')
    <style>
        .pdp-theme .settings-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
        }

        .pdp-theme .settings-header-copy {
            flex: 1 1 auto;
            min-width: 0;
        }

        .pdp-theme .settings-stats {
            width: 100%;
            max-width: 380px;
        }

        .pdp-theme .settings-stats .stat-item {
            padding: 10px 0;
            text-align: center;
        }

        .pdp-theme .settings-stats .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0;
            color: #fff;
        }

        .pdp-theme .settings-stats .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.75;
        }

        .pdp-theme .nav-tabs-custom {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 24px;
        }

        .pdp-theme .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            background: transparent;
            color: #6b7280;
            font-weight: 500;
            padding: 12px 18px;
            transition: all 0.2s ease;
            border-radius: 0;
        }

        .pdp-theme .nav-tabs-custom .nav-link:hover {
            color: #4e73df;
            background: transparent;
        }

        .pdp-theme .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            border-bottom-color: #4e73df;
            background: transparent;
        }

        .pdp-theme .tab-toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }

        .pdp-theme .tab-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            height: 100%;
        }

        .pdp-theme .tab-card-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .pdp-theme .tab-card-copy {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
            margin-bottom: 0;
        }

        .pdp-theme .settings-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .pdp-theme .settings-stack {
            display: grid;
            gap: 16px;
        }

        .pdp-theme .summary-table td,
        .pdp-theme .summary-table th {
            vertical-align: top;
        }

        .pdp-theme .text-chip {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 600;
            margin: 0 6px 6px 0;
        }

        .pdp-theme .table-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        .pdp-theme .table-actions form {
            margin: 0;
        }

        .pdp-theme .table-actions .btn {
            padding: 8px 12px;
            font-size: 12px;
        }

        .pdp-theme .workflow-card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            background: #fff;
        }

        .pdp-theme .workflow-card-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #1f2937;
        }

        .pdp-theme .workflow-card-copy {
            color: #4b5563;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 14px;
        }

        .pdp-theme .workflow-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 16px;
        }

        .pdp-theme .workflow-meta-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .pdp-theme .workflow-meta-value {
            color: #111827;
            font-size: 13px;
            line-height: 1.5;
        }

        .pdp-theme .comment-bank-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .pdp-theme .comment-bank-list {
            display: grid;
            gap: 10px;
            margin-top: 16px;
        }

        .pdp-theme .comment-bank-item {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 12px 14px;
            background: #f9fafb;
            color: #374151;
            font-size: 13px;
            line-height: 1.6;
        }

        .pdp-theme .comment-bank-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .pdp-theme .empty-icon {
            font-size: 46px;
            opacity: 0.3;
        }

        @media (max-width: 992px) {
            .pdp-theme .settings-header {
                flex-direction: column;
            }

            .pdp-theme .settings-stats {
                max-width: none;
            }
        }

        @media (max-width: 768px) {
            .pdp-theme .settings-grid,
            .pdp-theme .workflow-meta,
            .pdp-theme .comment-bank-grid {
                grid-template-columns: 1fr;
            }

            .pdp-theme .tab-toolbar {
                justify-content: flex-start;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Staff PDP
        @endslot
        @slot('li_1_url')
            {{ route('staff.pdp.plans.index') }}
        @endslot
        @slot('title')
            PDP Settings
        @endslot
    @endcomponent

    @php
        $tabHelp = $helpText[$activeTab];
        $activeTemplateStats = [
            'periods' => $activeTemplate?->periods?->count() ?? 0,
            'ratings' => $activeTemplate?->ratingSchemes?->count() ?? 0,
            'approvals' => $activeTemplate?->approvalSteps?->count() ?? 0,
        ];
    @endphp

    <div class="pdp-theme">
        <div class="page-shell">
            <div class="page-shell-header">
                <div class="settings-header">
                    <div class="settings-header-copy">
                        <div class="page-shell-title">PDP Settings</div>
                        <div class="page-subtitle">
                            Centralize template visibility, module defaults, review cadence, scoring references, access rules, and workflow guidance in one place.
                        </div>
                    </div>
                    <div class="settings-stats">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4>{{ $templateStats['total'] }}</h4>
                                    <small>Templates</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4>{{ $activeTemplateStats['periods'] }}</h4>
                                    <small>Periods</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4>{{ $activeTemplateStats['approvals'] }}</h4>
                                    <small>Approvals</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-shell-body">
                <div class="nav nav-tabs-custom">
                    @foreach ($tabs as $tabKey => $tab)
                        <a href="{{ route('staff.pdp.settings.index', ['tab' => $tabKey]) }}"
                            class="nav-link {{ $activeTab === $tabKey ? 'active' : '' }}">
                            <i class="{{ $tab['icon'] }}"></i>
                            {{ $tab['label'] }}
                        </a>
                    @endforeach
                </div>

                <div class="help-text">
                    <div class="help-title">{{ $tabHelp['title'] }}</div>
                    <div class="help-content">{{ $tabHelp['content'] }}</div>
                </div>

                @if ($activeTab === 'templates')
                    <div class="tab-toolbar">
                        <a href="{{ route('staff.pdp.templates.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus"></i> Create Draft Template
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle summary-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Family</th>
                                    <th>Version</th>
                                    <th>Status</th>
                                    <th>Plans</th>
                                    <th>Source</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($templates as $template)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $template->name }}</div>
                                            <div class="text-muted small">{{ $template->code }}</div>
                                        </td>
                                        <td>{{ $template->template_family_key }}</td>
                                        <td>v{{ $template->version }}</td>
                                        <td>
                                            <span class="badge-soft badge-soft-dark">{{ ucfirst($template->status) }}</span>
                                            @if ($template->is_default)
                                                <span class="badge-soft badge-soft-primary ms-1">Active Default</span>
                                            @endif
                                        </td>
                                        <td>{{ $template->plans_count }}</td>
                                        <td>{{ $template->source_reference ?: 'N/A' }}</td>
                                        <td class="text-end">
                                            <div class="table-actions">
                                                <a href="{{ route('staff.pdp.templates.show', $template) }}" class="btn btn-outline-primary">
                                                    <i class="bx bx-show"></i> View
                                                </a>
                                                <form method="POST" action="{{ route('staff.pdp.templates.clone', $template) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-light">
                                                        <i class="bx bx-copy"></i> Clone
                                                    </button>
                                                </form>
                                                @if ($template->status === 'draft')
                                                    <form method="POST" action="{{ route('staff.pdp.templates.publish', $template) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-light">
                                                            <i class="bx bx-upload"></i> Publish
                                                        </button>
                                                    </form>
                                                @endif
                                                @if ($template->status === 'published' && !$template->is_default)
                                                    <form method="POST" action="{{ route('staff.pdp.templates.activate', $template) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-light">
                                                            <i class="bx bx-check"></i> Activate & Apply
                                                        </button>
                                                    </form>
                                                @endif
                                                @if (!$template->is_default && $template->status !== 'archived')
                                                    <form method="POST" action="{{ route('staff.pdp.templates.archive', $template) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-light">
                                                            <i class="bx bx-archive"></i> Archive
                                                        </button>
                                                    </form>
                                                @endif
                                                @if (($template->plans_count + $template->rollouts_count) > 0)
                                                    <a href="{{ route('staff.pdp.templates.show', ['template' => $template, 'confirm_delete' => 1]) }}"
                                                        class="btn btn-outline-danger">
                                                        <i class="bx bx-trash"></i> Delete
                                                    </a>
                                                @else
                                                    <form method="POST" action="{{ route('staff.pdp.templates.destroy', $template) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger">
                                                            <i class="bx bx-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state">
                                                <div><i class="bx bx-layer empty-icon"></i></div>
                                                <p class="mt-3 mb-0">No PDP templates are available.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @elseif ($activeTab === 'rollouts')
                    <div class="settings-grid">
                        <div class="tab-card">
                            <div class="tab-card-title">Launch PDP Rollout</div>
                            <p class="tab-card-copy mb-4">
                                Start a school-wide PDP cycle from the active template. This provisions current active staff immediately and can continue auto-provisioning future staff.
                            </p>

                            @if (!$activeTemplate)
                                <div class="empty-state">
                                    <div><i class="bx bx-rocket empty-icon"></i></div>
                                    <p class="mt-3 mb-3">Activate a published PDP template before launching a rollout.</p>
                                    <a href="{{ route('staff.pdp.settings.index', ['tab' => 'templates']) }}" class="btn btn-primary">
                                        <i class="bx bx-layer"></i> Go To Templates
                                    </a>
                                </div>
                            @else
                                <form method="POST" action="{{ route('staff.pdp.rollouts.store') }}">
                                    @csrf
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label class="form-label">Rollout Label</label>
                                            <input type="text" name="label" class="form-control"
                                                value="{{ old('label', 'PDP ' . now()->year . ' Cycle') }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Cycle Year</label>
                                            <input type="number" name="cycle_year" min="2000" max="2100" class="form-control"
                                                value="{{ old('cycle_year', now()->year) }}" required>
                                        </div>
                                    </div>

                                    <div class="form-grid mt-3">
                                        <div class="form-group">
                                            <label class="form-label">Plan Period Start</label>
                                            <input type="date" name="plan_period_start" class="form-control"
                                                value="{{ old('plan_period_start', $suggestedDates['start']->format('Y-m-d')) }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Plan Period End</label>
                                            <input type="date" name="plan_period_end" class="form-control"
                                                value="{{ old('plan_period_end', $suggestedDates['end']->format('Y-m-d')) }}" required>
                                        </div>
                                    </div>

                                    <div class="form-grid mt-3">
                                        <div class="form-group">
                                            <label class="form-label">Fallback Supervisor</label>
                                            <select name="fallback_supervisor_user_id" class="form-select" required>
                                                <option value="">Select fallback supervisor</option>
                                                @foreach ($fallbackSupervisors as $fallbackSupervisor)
                                                    <option value="{{ $fallbackSupervisor->id }}"
                                                        @selected((string) old('fallback_supervisor_user_id', $activeRollout?->fallback_supervisor_user_id) === (string) $fallbackSupervisor->id)>
                                                        {{ $fallbackSupervisor->full_name }}{{ $fallbackSupervisor->position ? ' | ' . $fallbackSupervisor->position : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group d-flex align-items-end">
                                            <div class="form-check form-switch mb-2">
                                                <input type="hidden" name="auto_provision_new_staff" value="0">
                                                <input type="checkbox" class="form-check-input" name="auto_provision_new_staff"
                                                    id="auto_provision_new_staff" value="1"
                                                    @checked((bool) old('auto_provision_new_staff', $activeRollout?->auto_provision_new_staff ?? true))>
                                                <label class="form-check-label" for="auto_provision_new_staff">
                                                    Auto-provision future eligible staff
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="help-text mt-3">
                                        <div class="help-title">Active Template</div>
                                        <div class="help-content">
                                            {{ $activeTemplate->name }} ({{ $activeTemplate->code }}) is the template that will be snapshotted into all rollout-created plans.
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        @include('pdp.partials.submit-button', [
                                            'label' => 'Launch Rollout',
                                            'loadingText' => 'Launching rollout...',
                                            'icon' => 'bx bx-rocket',
                                            'variant' => 'btn-primary',
                                        ])
                                    </div>
                                </form>
                            @endif
                        </div>

                        <div class="settings-stack">
                            <div class="tab-card">
                                <div class="tab-card-title">Active Rollout</div>
                                @if ($activeRollout)
                                    <p class="tab-card-copy">
                                        {{ $activeRollout->label }} is currently active and bound to {{ $activeRollout->template->name }}.
                                    </p>
                                    <div class="header-meta mt-3">
                                        <div class="header-meta-item">
                                            <span class="header-meta-label">Cycle</span>
                                            <span class="header-meta-value">{{ $activeRollout->cycle_year ?: 'N/A' }}</span>
                                        </div>
                                        <div class="header-meta-item">
                                            <span class="header-meta-label">Period</span>
                                            <span class="header-meta-value">{{ $activeRollout->plan_period_start->format('Y-m-d') }} to {{ $activeRollout->plan_period_end->format('Y-m-d') }}</span>
                                        </div>
                                        <div class="header-meta-item">
                                            <span class="header-meta-label">Provisioned</span>
                                            <span class="header-meta-value">{{ data_get($activeRollout->summary_json, 'provisioned', $activeRollout->provisioned_count) }}</span>
                                        </div>
                                        <div class="header-meta-item">
                                            <span class="header-meta-label">Skipped</span>
                                            <span class="header-meta-value">{{ data_get($activeRollout->summary_json, 'skipped', $activeRollout->skipped_count) }}</span>
                                        </div>
                                        <div class="header-meta-item">
                                            <span class="header-meta-label">Fallback Supervisor</span>
                                            <span class="header-meta-value">{{ $activeRollout->fallbackSupervisor?->full_name ?? 'Not set' }}</span>
                                        </div>
                                        <div class="header-meta-item">
                                            <span class="header-meta-label">Future Staff</span>
                                            <span class="header-meta-value">{{ $activeRollout->auto_provision_new_staff ? 'Enabled' : 'Disabled' }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <a href="{{ route('staff.pdp.rollouts.show', $activeRollout) }}" class="btn btn-outline-primary">
                                            <i class="bx bx-show"></i> View Rollout
                                        </a>
                                    </div>
                                @else
                                    <div class="empty-state">
                                        <div><i class="bx bx-rocket empty-icon"></i></div>
                                        <p class="mt-3 mb-0">No active PDP rollout is running yet.</p>
                                    </div>
                                @endif
                            </div>

                            <div class="tab-card">
                                <div class="tab-card-title">Rollout History</div>
                                @if ($rollouts->isEmpty())
                                    <div class="empty-state">
                                        <div><i class="bx bx-history empty-icon"></i></div>
                                        <p class="mt-3 mb-0">No rollout history is available yet.</p>
                                    </div>
                                @else
                                    <div class="list-group list-group-flush">
                                        @foreach ($rollouts->take(6) as $rollout)
                                            <a href="{{ route('staff.pdp.rollouts.show', $rollout) }}"
                                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-start px-0">
                                                <div>
                                                    <div class="fw-semibold">{{ $rollout->label }}</div>
                                                    <div class="text-muted small">
                                                        {{ $rollout->template->code }} | {{ ucfirst($rollout->status) }} | {{ $rollout->plans_count }} plans
                                                    </div>
                                                </div>
                                                <span class="badge-soft badge-soft-dark">{{ $rollout->cycle_year ?: 'N/A' }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @elseif ($activeTab === 'general')
                    <div class="settings-grid">
                        <div class="tab-card">
                            <div class="tab-card-title">General PDP Defaults</div>
                            <p class="tab-card-copy mb-4">
                                These settings control support copy, Part A employee-information defaults, general guidance, and date suggestions for newly created PDP plans.
                            </p>

                            <form method="POST" action="{{ route('staff.pdp.settings.update', 'general') }}">
                                @csrf
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Support Label</label>
                                        <input type="text" name="active_template_support_label" class="form-control"
                                            value="{{ old('active_template_support_label', $generalSettings['active_template_support_label']) }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Support Contact</label>
                                        <input type="text" name="active_template_support_contact" class="form-control"
                                            value="{{ old('active_template_support_contact', $generalSettings['active_template_support_contact']) }}"
                                            placeholder="HR office, School Head, or support email">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Part A Ministry / Department</label>
                                        <input type="text" name="part_a_ministry_department" class="form-control"
                                            value="{{ old('part_a_ministry_department', $generalSettings['part_a_ministry_department']) }}"
                                            placeholder="Secondary">
                                    </div>
                                </div>

                                <div class="form-grid mt-3">
                                    <div class="form-group">
                                        <label class="form-label">Suggested Start Month</label>
                                        <input type="number" min="1" max="12" name="default_plan_start_month" class="form-control"
                                            value="{{ old('default_plan_start_month', $generalSettings['default_plan_start_month']) }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Suggested Start Day</label>
                                        <input type="number" min="1" max="31" name="default_plan_start_day" class="form-control"
                                            value="{{ old('default_plan_start_day', $generalSettings['default_plan_start_day']) }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Suggested End Month</label>
                                        <input type="number" min="1" max="12" name="default_plan_end_month" class="form-control"
                                            value="{{ old('default_plan_end_month', $generalSettings['default_plan_end_month']) }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Suggested End Day</label>
                                        <input type="number" min="1" max="31" name="default_plan_end_day" class="form-control"
                                            value="{{ old('default_plan_end_day', $generalSettings['default_plan_end_day']) }}">
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label class="form-label">Support Note</label>
                                    <textarea name="active_template_support_note" class="form-control" rows="4">{{ old('active_template_support_note', $generalSettings['active_template_support_note']) }}</textarea>
                                </div>

                                <div class="form-group mt-3">
                                    <label class="form-label">General Guidance</label>
                                    <textarea name="general_guidance" class="form-control" rows="8" placeholder="Add one guidance point per line.">{{ old('general_guidance', $generalSettings['general_guidance']) }}</textarea>
                                    <small class="text-muted d-block mt-2">
                                        This text appears above Part A on staff PDP plans and the printable/PDF version.
                                    </small>
                                </div>

                                <div class="form-actions">
                                    @include('pdp.partials.submit-button', [
                                        'label' => 'Save General Settings',
                                        'loadingText' => 'Saving settings...',
                                        'icon' => 'fas fa-save',
                                    ])
                                </div>
                            </form>
                        </div>

                        <div class="settings-stack">
                            <div class="tab-card">
                                <div class="tab-card-title">Active Template Summary</div>
                                <p class="tab-card-copy">
                                    {{ $activeTemplate ? $activeTemplate->name . ' v' . $activeTemplate->version : 'No active template has been activated yet.' }}
                                </p>
                                @if ($activeTemplate)
                                    <div class="header-meta mt-3">
                                        <div class="header-meta-item">
                                            <span class="header-meta-label">Family</span>
                                            <span class="header-meta-value">{{ $activeTemplate->template_family_key }}</span>
                                        </div>
                                        <div class="header-meta-item">
                                            <span class="header-meta-label">Code</span>
                                            <span class="header-meta-value">{{ $activeTemplate->code }}</span>
                                        </div>
                                        <div class="header-meta-item">
                                            <span class="header-meta-label">Periods</span>
                                            <span class="header-meta-value">{{ $activeTemplateStats['periods'] }}</span>
                                        </div>
                                        <div class="header-meta-item">
                                            <span class="header-meta-label">Ratings</span>
                                            <span class="header-meta-value">{{ $activeTemplateStats['ratings'] }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="tab-card">
                                <div class="tab-card-title">Suggested Plan Dates</div>
                                <p class="tab-card-copy">
                                    New plan forms currently suggest <strong>{{ $suggestedDates['start']->format('Y-m-d') }}</strong> to
                                    <strong>{{ $suggestedDates['end']->format('Y-m-d') }}</strong> for the current year.
                                </p>
                            </div>
                        </div>
                    </div>
                @elseif ($activeTab === 'comments-bank')
                    <form method="POST" action="{{ route('staff.pdp.settings.update', 'comments-bank') }}">
                        @csrf
                        <div class="comment-bank-grid">
                            <div class="tab-card">
                                <div class="tab-card-title">Supervisee Canned Comments</div>
                                <p class="tab-card-copy mb-4">
                                    Add one comment per line. These suggestions appear on supervisee comment fields when staff review their own PDP objectives.
                                </p>

                                <div class="comment-bank-meta">
                                    <span class="badge-soft badge-soft-primary">{{ count($commentBank['supervisee_comments']) }} comments</span>
                                    <span class="text-muted small">Shown to the employee side of the review.</span>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Supervisee Comments</label>
                                    <textarea name="supervisee_comments" class="form-control" rows="16">{{ old('supervisee_comments', implode("\n", $commentBank['supervisee_comments'])) }}</textarea>
                                    <div class="help-content mt-2">Each line is saved as a separate canned comment option.</div>
                                </div>
                            </div>

                            <div class="tab-card">
                                <div class="tab-card-title">Supervisor Canned Comments</div>
                                <p class="tab-card-copy mb-4">
                                    Add one comment per line. These suggestions appear on supervisor comment fields when reporting officers or PDP administrators review a plan.
                                </p>

                                <div class="comment-bank-meta">
                                    <span class="badge-soft badge-soft-success">{{ count($commentBank['supervisor_comments']) }} comments</span>
                                    <span class="text-muted small">Shown to the supervisor side of the review.</span>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Supervisor Comments</label>
                                    <textarea name="supervisor_comments" class="form-control" rows="16">{{ old('supervisor_comments', implode("\n", $commentBank['supervisor_comments'])) }}</textarea>
                                    <div class="help-content mt-2">Keep comments concise and reusable across common PDP review situations.</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions mt-4">
                            @include('pdp.partials.submit-button', [
                                'label' => 'Save Canned Comments',
                                'loadingText' => 'Saving canned comments...',
                                'icon' => 'fas fa-save',
                            ])
                        </div>
                    </form>

                    <div class="comment-bank-grid mt-4">
                        <div class="tab-card">
                            <div class="tab-card-title">Supervisee Preview</div>
                            <p class="tab-card-copy">These are the ready-made comments currently available to supervisees inside PDP plans.</p>

                            @if ($commentBank['supervisee_comments'] === [])
                                <div class="empty-state mt-4">
                                    <div><i class="bx bx-comment-x empty-icon"></i></div>
                                    <p class="mt-3 mb-0">No supervisee canned comments are configured.</p>
                                </div>
                            @else
                                <div class="comment-bank-list">
                                    @foreach ($commentBank['supervisee_comments'] as $comment)
                                        <div class="comment-bank-item">{{ $comment }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="tab-card">
                            <div class="tab-card-title">Supervisor Preview</div>
                            <p class="tab-card-copy">These are the ready-made comments currently available to supervisors inside PDP plans.</p>

                            @if ($commentBank['supervisor_comments'] === [])
                                <div class="empty-state mt-4">
                                    <div><i class="bx bx-comment-x empty-icon"></i></div>
                                    <p class="mt-3 mb-0">No supervisor canned comments are configured.</p>
                                </div>
                            @else
                                <div class="comment-bank-list">
                                    @foreach ($commentBank['supervisor_comments'] as $comment)
                                        <div class="comment-bank-item">{{ $comment }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif ($activeTab === 'review-periods')
                    @if ($periodRows->isEmpty())
                        <div class="empty-state">
                            <div><i class="bx bx-calendar-x empty-icon"></i></div>
                            <p class="mt-3 mb-0">No active template periods are available yet.</p>
                        </div>
                    @else
                        <div class="tab-toolbar">
                            <a href="{{ $activeTemplate ? route('staff.pdp.templates.show', $activeTemplate) : route('staff.pdp.templates.create') }}"
                                class="btn btn-primary">
                                <i class="bx bx-edit-alt"></i> Manage Through Template Draft
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle summary-table">
                                <thead>
                                    <tr>
                                        <th>Label</th>
                                        <th>Key</th>
                                        <th>Sequence</th>
                                        <th>Window Type</th>
                                        <th>Open Rule</th>
                                        <th>Close Rule</th>
                                        <th>Summary Label</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($periodRows as $period)
                                        <tr>
                                            <td class="fw-semibold">{{ $period['label'] }}</td>
                                            <td>{{ $period['key'] }}</td>
                                            <td>{{ $period['sequence'] }}</td>
                                            <td>{{ $period['window_type'] }}</td>
                                            <td>{{ $period['open_rule'] }}</td>
                                            <td>{{ $period['close_rule'] }}</td>
                                            <td>{{ $period['summary_label'] ?: 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @elseif ($activeTab === 'scoring-ratings')
                    @if ($ratingRows->isEmpty())
                        <div class="empty-state">
                            <div><i class="bx bx-line-chart-down empty-icon"></i></div>
                            <p class="mt-3 mb-0">No active template rating schemes are available yet.</p>
                        </div>
                    @else
                        <div class="tab-toolbar">
                            <a href="{{ $activeTemplate ? route('staff.pdp.templates.show', $activeTemplate) : route('staff.pdp.templates.create') }}"
                                class="btn btn-primary">
                                <i class="bx bx-edit-alt"></i> Manage Through Template Draft
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle summary-table">
                                <thead>
                                    <tr>
                                        <th>Label</th>
                                        <th>Key</th>
                                        <th>Input Type</th>
                                        <th>Weight</th>
                                        <th>Rounding</th>
                                        <th>Scale</th>
                                        <th>Formula</th>
                                        <th>Bands</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ratingRows as $scheme)
                                        <tr>
                                            <td class="fw-semibold">{{ $scheme['label'] }}</td>
                                            <td>{{ $scheme['key'] }}</td>
                                            <td>{{ $scheme['input_type'] }}</td>
                                            <td>{{ $scheme['weight'] ?? 'N/A' }}</td>
                                            <td>{{ $scheme['rounding_rule'] ?: 'N/A' }}</td>
                                            <td>{{ $scheme['scale'] }}</td>
                                            <td>{{ $scheme['formula'] }}</td>
                                            <td>{{ $scheme['bands'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @elseif ($activeTab === 'approvals-signatures')
                    <div class="settings-grid">
                        <div class="tab-card">
                            <div class="tab-card-title">Active Approval Chain</div>
                            <p class="tab-card-copy mb-4">
                                The approval steps below come from the active template definition.
                            </p>

                            @if ($approvalRows->isEmpty())
                                <div class="empty-state">
                                    <div><i class="bx bx-check-shield empty-icon"></i></div>
                                    <p class="mt-3 mb-0">No active approval steps are configured yet.</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table align-middle summary-table">
                                        <thead>
                                            <tr>
                                                <th>Step</th>
                                                <th>Role Type</th>
                                                <th>Required</th>
                                                <th>Comment</th>
                                                <th>Period Scope</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($approvalRows as $step)
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">{{ $step['sequence'] }}. {{ $step['label'] }}</div>
                                                        <div class="text-muted small">{{ $step['key'] }}</div>
                                                    </td>
                                                    <td>{{ $step['role_type'] }}</td>
                                                    <td>{{ $step['required'] ? 'Required' : 'Optional' }}</td>
                                                    <td>{{ $step['comment_required'] ? 'Required' : 'Optional' }}</td>
                                                    <td>{{ $step['period_scope'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <div class="tab-card">
                            <div class="tab-card-title">Elevated PDP Access</div>
                            <p class="tab-card-copy mb-4">
                                These module settings control who is treated as an elevated PDP administrator for templates, reports, and broad plan visibility.
                            </p>

                            <form method="POST" action="{{ route('staff.pdp.settings.update', 'approvals-signatures') }}">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">Elevated Positions</label>
                                    <textarea name="elevated_positions" class="form-control" rows="5">{{ old('elevated_positions', implode("\n", $accessSettings['elevated_positions'])) }}</textarea>
                                    @if ($availablePositions !== [])
                                        <div class="mt-3">
                                            @foreach ($availablePositions as $position)
                                                <span class="text-chip">{{ $position }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group mt-3">
                                    <label class="form-label">Elevated Roles</label>
                                    <textarea name="elevated_roles" class="form-control" rows="6">{{ old('elevated_roles', implode("\n", $accessSettings['elevated_roles'])) }}</textarea>
                                    @if ($availableRoles !== [])
                                        <div class="mt-3">
                                            @foreach ($availableRoles as $role)
                                                <span class="text-chip">{{ $role }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="form-actions">
                                    @include('pdp.partials.submit-button', [
                                        'label' => 'Save Access Settings',
                                        'loadingText' => 'Saving access settings...',
                                        'icon' => 'fas fa-save',
                                    ])
                                </div>
                            </form>
                        </div>
                    </div>
                @elseif ($activeTab === 'workflow')
                    <div class="settings-stack">
                        @foreach ($workflowSteps as $step)
                            <div class="workflow-card">
                                <div class="workflow-card-title">{{ $step['title'] }}</div>
                                <p class="workflow-card-copy">{{ $step['body'] }}</p>
                                <div class="workflow-meta">
                                    @foreach ($step['meta'] as $label => $value)
                                        <div>
                                            <span class="workflow-meta-label">{{ $label }}</span>
                                            <div class="workflow-meta-value">{{ $value }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="tab-card">
                                <div class="tab-card-title">Part A Preview Copy</div>
                                <p class="tab-card-copy mb-3">
                                    These values are rendered above the employee information section for staff PDP plans.
                                </p>
                                <div class="workflow-meta">
                                    <div>
                                        <span class="workflow-meta-label">Ministry / Department</span>
                                        <div class="workflow-meta-value">{{ $generalSettings['part_a_ministry_department'] ?: 'Not set' }}</div>
                                    </div>
                                    <div>
                                        <span class="workflow-meta-label">Guidance Lines</span>
                                        <div class="workflow-meta-value">
                                            {{ collect(preg_split('/\r\n|\r|\n/', (string) ($generalSettings['general_guidance'] ?? '')))->map(fn ($line) => trim($line))->filter()->count() }}
                                        </div>
                                    </div>
                                </div>
                                @php
                                    $guidancePreviewLines = collect(preg_split('/\r\n|\r|\n/', (string) ($generalSettings['general_guidance'] ?? '')))
                                        ->map(fn ($line) => trim($line))
                                        ->filter()
                                        ->values();
                                @endphp
                                @if ($guidancePreviewLines->isNotEmpty())
                                    <div class="help-text mt-3 mb-0">
                                        <div class="help-title">Current General Guidance</div>
                                        <div class="help-content">
                                            <ol class="mb-0 ps-3">
                                                @foreach ($guidancePreviewLines as $guidanceLine)
                                                    <li>{{ $guidanceLine }}</li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
@endsection

@section('script')
    @include('pdp.partials.theme-script')
@endsection

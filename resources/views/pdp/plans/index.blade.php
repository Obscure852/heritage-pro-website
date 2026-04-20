@extends('layouts.master')

@section('title', 'PDP Plans')
@section('page_title', 'PDP Plans')
@section('css')
    @include('pdp.partials.theme-css')
    <style>
        .pdp-theme .plans-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .pdp-theme .plans-header-copy {
            flex: 1 1 auto;
            min-width: 0;
        }

        .pdp-theme .plans-stats {
            width: 100%;
            max-width: 360px;
        }

        .pdp-theme .plans-stats .stat-item {
            padding: 10px 0;
            text-align: center;
        }

        .pdp-theme .plans-stats .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0;
            color: #fff;
        }

        .pdp-theme .plans-stats .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.75;
        }

        .pdp-theme .filter-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: flex-start;
            gap: 16px;
        }

        .pdp-theme .filter-toolbar .form-group {
            flex: 0 1 180px;
            min-width: 160px;
            max-width: 220px;
        }

        .pdp-theme .filter-toolbar .form-group.filter-template {
            flex-basis: 240px;
            max-width: 260px;
        }

        .pdp-theme .filter-toolbar .filter-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 12px;
            margin: 0;
            padding: 0;
            border: 0;
        }

        .pdp-theme .filter-toolbar .toolbar-create {
            margin-left: auto;
        }

        .pdp-theme .empty-state-cell {
            padding: 40px 0;
        }

        .pdp-theme .empty-state-cell .btn {
            margin-top: 18px;
        }

        .pdp-theme .empty-state-cell .empty-icon {
            font-size: 48px;
            opacity: 0.3;
        }

        .pdp-theme .empty-state-cell .empty-copy {
            font-size: 15px;
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .pdp-theme .plans-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .pdp-theme .plans-stats {
                max-width: none;
            }

            .pdp-theme .filter-toolbar .form-group,
            .pdp-theme .filter-toolbar .form-group.filter-template,
            .pdp-theme .filter-toolbar .filter-actions,
            .pdp-theme .filter-toolbar .toolbar-create {
                flex: 1 1 100%;
                max-width: none;
            }

            .pdp-theme .filter-toolbar .toolbar-create {
                margin-left: 0;
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
            {{ route('staff.pdp.my') }}
        @endslot
        @slot('title')
            PDP Plans
        @endslot
    @endcomponent

    <div class="pdp-theme">
        <div class="page-shell">
            <div class="page-shell-header">
                <div class="plans-header">
                    <div class="plans-header-copy">
                    <div class="page-shell-title">PDP Plans</div>
                    <div class="page-subtitle">
                        Review rollout-generated Staff PDP plans, filter by status or template version, and confirm which template definition each plan is bound to.
                    </div>
                    </div>
                    <div class="plans-stats">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4>{{ $plans->count() }}</h4>
                                    <small>Accessible</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4>{{ $plans->where('status', 'active')->count() }}</h4>
                                    <small>Active</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4>{{ $plans->where('status', 'completed')->count() }}</h4>
                                    <small>Completed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-shell-body">
                <div class="help-text">
                    <div class="help-title">Audit-Friendly Plan Listing</div>
                    <div class="help-content">
                        The list below shows the exact template code and version used by each plan so older records remain traceable even after new templates or new rollouts are activated.
                        @if ($activeRollout)
                            Active rollout: <strong>{{ $activeRollout->label }}</strong> ({{ $activeRollout->plan_period_start->format('Y-m-d') }} to {{ $activeRollout->plan_period_end->format('Y-m-d') }}).
                        @endif
                    </div>
                </div>

                <form method="GET" action="{{ route('staff.pdp.plans.index') }}">
                    <div class="filter-toolbar">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All statuses</option>
                                @foreach (['draft' => 'Draft', 'active' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}" @selected(($filters['status'] ?? '') === $statusValue)>{{ $statusLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group filter-template">
                            <label class="form-label">Template</label>
                            <select name="template_id" class="form-select">
                                <option value="">All templates</option>
                                @foreach ($filterTemplates as $template)
                                    <option value="{{ $template->id }}" @selected((string) ($filters['template_id'] ?? '') === (string) $template->id)>
                                        {{ $template->name }} (v{{ $template->version }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Employee</label>
                            <select name="user_id" class="form-select">
                                <option value="">All employees</option>
                                @foreach ($filterUsers as $filterUser)
                                    <option value="{{ $filterUser->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $filterUser->id)>
                                        {{ $filterUser->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn btn-light">
                                <i class="bx bx-filter-alt"></i> Apply Filters
                            </button>
                            <a href="{{ route('staff.pdp.plans.index') }}" class="btn btn-secondary">
                                <i class="bx bx-reset"></i> Reset
                            </a>
                        </div>

                        @if ($canManageRollouts)
                            <a href="{{ route('staff.pdp.settings.index', ['tab' => 'rollouts']) }}" class="btn btn-primary toolbar-create">
                                <i class="bx bx-rocket"></i> Run Rollout
                            </a>
                        @endif
                        @if ($canCreateManualPlans)
                            <a href="{{ route('staff.pdp.plans.create') }}" class="btn btn-light">
                                <i class="bx bx-plus"></i> Manual Plan
                            </a>
                        @endif
                    </div>
                </form>

                <div class="table-responsive mt-4">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Template</th>
                                <th>Version</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Current Period</th>
                                <th>Updated</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($plans as $plan)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $plan->user->full_name }}</div>
                                        <div class="text-muted small">{{ $plan->user->position }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $plan->template->name }}</div>
                                        <div class="text-muted small">{{ $plan->template->code }}</div>
                                    </td>
                                    <td>v{{ $plan->template->version }}</td>
                                    <td>{{ $plan->plan_period_start->format('Y-m-d') }} to {{ $plan->plan_period_end->format('Y-m-d') }}</td>
                                    <td><span class="badge-soft badge-soft-dark">{{ ucfirst($plan->status) }}</span></td>
                                    <td>{{ $plan->current_period_key ?? 'None' }}</td>
                                    <td>{{ $plan->updated_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                                    <td class="text-end">
                                        <div class="action-buttons">
                                            <a href="{{ route('staff.pdp.plans.show', $plan) }}" class="btn btn-outline-primary" title="Open Plan">
                                                <i class="bx bx-right-arrow-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="text-center text-muted empty-state-cell">
                                            <div>
                                                <i class="bx bx-clipboard empty-icon"></i>
                                            </div>
                                            <p class="mt-3 empty-copy">No PDP plans are available yet.</p>
                                            @if ($canManageRollouts)
                                                <a href="{{ route('staff.pdp.settings.index', ['tab' => 'rollouts']) }}" class="btn btn-primary">
                                                    <i class="bx bx-rocket me-1"></i> Run Rollout
                                                </a>
                                            @elseif ($canCreateManualPlans)
                                                <a href="{{ route('staff.pdp.plans.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus me-1"></i> Manual Plan
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

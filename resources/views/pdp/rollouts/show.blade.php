@extends('layouts.master')

@section('title', 'PDP Rollout')
@section('page_title', 'PDP Rollout')
@section('css')
    @include('pdp.partials.theme-css')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            PDP Settings
        @endslot
        @slot('li_1_url')
            {{ route('staff.pdp.settings.index', ['tab' => 'rollouts']) }}
        @endslot
        @slot('title')
            PDP Rollout
        @endslot
    @endcomponent

    <div class="pdp-theme">
        <div class="page-shell mb-4">
            <div class="page-shell-header d-flex flex-wrap justify-content-between gap-4 align-items-start">
                <div>
                    <div class="page-shell-title">{{ $rollout->label }}</div>
                    <div class="page-subtitle">
                        {{ $rollout->template->name }} | {{ ucfirst($rollout->status) }} | {{ ucfirst($rollout->provisioning_status) }}
                    </div>
                    <div class="header-meta">
                        <div class="header-meta-item">
                            <span class="header-meta-label">Cycle</span>
                            <span class="header-meta-value">{{ $rollout->cycle_year ?: 'N/A' }}</span>
                        </div>
                        <div class="header-meta-item">
                            <span class="header-meta-label">Period</span>
                            <span class="header-meta-value">{{ $rollout->plan_period_start->format('Y-m-d') }} to {{ $rollout->plan_period_end->format('Y-m-d') }}</span>
                        </div>
                        <div class="header-meta-item">
                            <span class="header-meta-label">Fallback Supervisor</span>
                            <span class="header-meta-value">{{ $rollout->fallbackSupervisor?->full_name ?? 'Not assigned' }}</span>
                        </div>
                        <div class="header-meta-item">
                            <span class="header-meta-label">Future Staff</span>
                            <span class="header-meta-value">{{ $rollout->auto_provision_new_staff ? 'Auto-provision enabled' : 'Auto-provision disabled' }}</span>
                        </div>
                    </div>
                </div>

                <div class="summary-grid" style="max-width: 420px;">
                    <div class="summary-card">
                        <div class="summary-card-body">
                            <div class="display-label">Provisioned</div>
                            <div class="display-value">{{ data_get($rollout->summary_json, 'provisioned', $rollout->provisioned_count) }}</div>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-card-body">
                            <div class="display-label">Skipped</div>
                            <div class="display-value">{{ data_get($rollout->summary_json, 'skipped', $rollout->skipped_count) }}</div>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-card-body">
                            <div class="display-label">Fallback Used</div>
                            <div class="display-value">{{ data_get($rollout->summary_json, 'fallback_assigned', 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="page-shell-body">
                <div class="help-text mb-0">
                    <div class="help-title">Rollout Snapshot</div>
                    <div class="help-content">
                        Plans created by this rollout stay bound to {{ $rollout->template->code }} even if a newer template or rollout becomes active later.
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="section-panel">
                    <div class="section-panel-header">
                        <div class="section-panel-title">Provisioned Plans</div>
                        <p class="section-panel-subtitle mb-0">Plans generated directly by this rollout.</p>
                    </div>
                    <div class="section-panel-body">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Supervisor</th>
                                        <th>Status</th>
                                        <th class="text-end"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rollout->plans as $plan)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $plan->user?->full_name ?? 'Unknown user' }}</div>
                                                <div class="text-muted small">{{ $plan->user?->position ?: 'No position' }}</div>
                                            </td>
                                            <td>{{ $plan->supervisor?->full_name ?? 'Not assigned' }}</td>
                                            <td><span class="badge-soft badge-soft-dark">{{ ucfirst($plan->status) }}</span></td>
                                            <td class="text-end">
                                                <a href="{{ route('staff.pdp.plans.show', $plan) }}" class="btn btn-outline-primary">
                                                    <i class="bx bx-right-arrow-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4">
                                                <div class="empty-state">No plans were provisioned for this rollout.</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="section-panel">
                    <div class="section-panel-header">
                        <div class="section-panel-title">Skipped / Exceptions</div>
                        <p class="section-panel-subtitle mb-0">Staff skipped during launch with the recorded reason.</p>
                    </div>
                    <div class="section-panel-body">
                        @if (blank($rollout->exceptions_json))
                            <div class="empty-state">No rollout exceptions were recorded.</div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($rollout->exceptions_json as $exception)
                                    <div class="list-group-item px-0">
                                        <div class="fw-semibold">{{ $exception['user_name'] ?? 'Unknown user' }}</div>
                                        <div class="text-muted small">{{ $exception['reason'] ?? 'No reason provided' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('pdp.partials.theme-script')
@endsection

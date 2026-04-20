@extends('layouts.master')

@section('title', 'My PDP')
@section('page_title', 'My PDP')
@section('css')
    @include('pdp.partials.theme-css')
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
            My PDP
        @endslot
    @endcomponent

    <div class="pdp-theme my-pdp-page">
        <div class="page-shell">
            <div class="page-shell-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="my-pdp-header-title">My PDP</h3>
                        <p class="my-pdp-header-subtitle">Review the plans assigned to you and track their current workflow stage.</p>
                    </div>
                    <div class="col-md-6">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $plans->count() }}</h4>
                                    <small class="opacity-75">Total</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $plans->where('status', 'active')->count() }}</h4>
                                    <small class="opacity-75">Active</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $plans->where('status', 'completed')->count() }}</h4>
                                    <small class="opacity-75">Completed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-shell-body">
                <div class="help-text">
                    <div class="help-title">Plan Workspace</div>
                    <div class="help-content">
                        Use this page to keep track of your assigned PDP plans, open the current plan you are working on, and check whether a review is still active or already completed.
                    </div>
                </div>

                @if ($plans->isNotEmpty())
                    <div class="row g-4">
                        @foreach ($plans as $plan)
                            <div class="col-md-6 col-xl-4">
                                <div class="summary-card h-100">
                                    <div class="summary-card-header">
                                        <div class="d-flex justify-content-between align-items-start gap-3">
                                            <div>
                                                <div class="display-label">Template</div>
                                                <div class="display-value">{{ $plan->template->name }}</div>
                                                <div class="text-muted small">{{ $plan->template->code }} | v{{ $plan->template->version }}</div>
                                            </div>
                                            <span class="badge-soft badge-soft-dark">{{ ucfirst($plan->status) }}</span>
                                        </div>
                                    </div>
                                    <div class="summary-card-body">
                                        <div class="mb-3">
                                            <div class="display-label">Employee</div>
                                            <div class="display-value">{{ $plan->user->full_name }}</div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="display-label">Plan Period</div>
                                            <div>{{ $plan->plan_period_start->format('Y-m-d') }} to {{ $plan->plan_period_end->format('Y-m-d') }}</div>
                                        </div>
                                        <div class="mb-4">
                                            <div class="display-label">Current Period</div>
                                            <div>{{ $plan->current_period_key ?? 'None' }}</div>
                                        </div>

                                        <a href="{{ route('staff.pdp.plans.show', $plan) }}" class="btn btn-primary">
                                            <i class="bx bx-right-arrow-alt"></i> Open Plan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="page-empty-state">
                        <div class="empty-state-illustrated">
                            <div class="empty-state-icon">
                                <i class="bx bx-folder-open"></i>
                            </div>
                            <div class="empty-state-title">No PDP plans yet</div>
                            <div class="empty-state-copy">
                                Your assigned PDP plans will appear here once a plan has been created and shared with you.
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

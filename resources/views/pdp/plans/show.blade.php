@extends('layouts.master')

@section('title', 'PDP Plan')
@section('page_title', 'PDP Plan')
@section('css')
    @include('pdp.partials.theme-css')
@endsection

@section('content')
    @inject('pdpAccess', 'App\Services\Pdp\PdpAccessService')
    @inject('pdpSettings', 'App\Services\Pdp\PdpSettingsService')
    @php
        $currentPeriodLabel = $plan->current_period_key ? $viewService->periodLabel($plan->current_period_key) : 'None';
        $commentBank = $pdpSettings->commentBank();
    @endphp
    @component('components.breadcrumb')
        @slot('li_1')
            PDP Plans
        @endslot
        @slot('li_1_url')
            {{ route('staff.pdp.plans.index') }}
        @endslot
        @slot('title')
            PDP Plan
        @endslot
    @endcomponent

    <div class="pdp-theme plan-page">
        <div class="page-shell mb-4">
            <div class="page-shell-header">
                <div class="row align-items-center g-4">
                    <div class="col-lg-6">
                        <div class="page-shell-title">{{ $plan->user->full_name }}</div>
                        <div class="page-subtitle">
                            {{ $plan->user->position ?: 'No position on file' }} | {{ $plan->plan_period_start->format('Y-m-d') }} to {{ $plan->plan_period_end->format('Y-m-d') }}
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="plan-header-stats">
                            <div class="plan-header-stat">
                                <div class="plan-header-stat-value">{{ $plan->template->code }} | v{{ $plan->template->version }}</div>
                                <div class="plan-header-stat-label">Template</div>
                            </div>
                            <div class="plan-header-stat">
                                <div class="plan-header-stat-value">{{ ucfirst($plan->status) }}</div>
                                <div class="plan-header-stat-label">Status</div>
                            </div>
                            <div class="plan-header-stat">
                                <div class="plan-header-stat-value">{{ $currentPeriodLabel }}</div>
                                <div class="plan-header-stat-label">Current Period</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-shell-body">
                <div class="plan-overview-panel">
                    <div class="plan-overview-intro">
                        <div class="help-text mb-0">
                            <div class="help-title">Plan Review and Export Actions</div>
                            <div class="help-content">
                                Use this page to review the employee's bound PDP record, track review progress against the active template version, update sections where your role allows edits, and generate print or PDF copies when needed.
                            </div>
                        </div>
                    </div>

                    <div class="plan-action-row">
                        @if ($pdpAccess->canAdministerPlan($plan, auth()->user()))
                            <a href="{{ route('staff.pdp.plans.edit', $plan) }}" class="btn btn-light">
                                <i class="bx bx-edit"></i> Edit Plan
                            </a>
                        @endif
                        <a href="{{ route('staff.pdp.plans.print', $plan) }}" class="btn btn-light" target="_blank">
                            <i class="bx bx-show-alt"></i> Preview Print
                        </a>
                        <a href="{{ route('staff.pdp.plans.pdf', $plan) }}" class="btn btn-primary">
                            <i class="bx bx-download"></i> Download PDF
                        </a>
                        @if ($pdpAccess->canManageTemplates(auth()->user()))
                            <a href="{{ route('staff.pdp.templates.show', $plan->template) }}" class="btn btn-light">
                                <i class="bx bx-layer"></i> Template Version
                            </a>
                        @endif
                    </div>

                    <div class="plan-facts-grid">
                        <div class="plan-fact">
                            <div class="display-label">Template Name</div>
                            <div class="display-value">{{ $plan->template->name }}</div>
                        </div>
                        <div class="plan-fact">
                            <div class="display-label">Year-End Total</div>
                            <div class="display-value">{{ data_get($plan->calculated_summary_json, 'summary.year_end_total', 'N/A') }}</div>
                        </div>
                        <div class="plan-fact">
                            <div class="display-label">Final Rating</div>
                            <div class="display-value">{{ data_get($plan->calculated_summary_json, 'summary.final_rating_band', data_get($plan->calculated_summary_json, 'summary.final_rating', 'N/A')) }}</div>
                        </div>
                    </div>

                    <div class="plan-review-section">
                        <div class="plan-review-header">
                            <div>
                                <div class="section-panel-title">Review Timeline</div>
                                <p class="section-panel-subtitle mb-0">Open, close, and score reviews with the configured period flow.</p>
                            </div>
                        </div>

                        <div class="plan-review-list">
                            @foreach ($reviews as $review)
                                @php
                                    $reviewStatusClass = match ($review->status) {
                                        \App\Models\Pdp\PdpPlanReview::STATUS_OPEN => 'badge-soft-primary',
                                        \App\Models\Pdp\PdpPlanReview::STATUS_CLOSED => 'badge-soft-success',
                                        default => 'badge-soft-warning',
                                    };
                                @endphp
                                <div class="plan-review-item">
                                    <div class="plan-review-copy">
                                        <div class="plan-review-title-row">
                                            <div class="display-value">{{ $viewService->periodLabel($review->period_key) }}</div>
                                            <span class="badge-soft {{ $reviewStatusClass }}">{{ ucfirst($review->status) }}</span>
                                        </div>
                                        @if (is_array($review->score_summary_json))
                                            <div class="plan-review-meta">
                                                <span>Total: {{ data_get($review->score_summary_json, 'total_score', 'N/A') }}</span>
                                                @if (data_get($review->score_summary_json, 'rating_band'))
                                                    <span>Band: {{ data_get($review->score_summary_json, 'rating_band') }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="plan-review-meta">
                                                <span>No score recorded yet.</span>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($review_permissions['can_manage_reviews'])
                                        <div class="plan-review-action">
                                            @if ($review->status === \App\Models\Pdp\PdpPlanReview::STATUS_PENDING)
                                                <form method="POST" action="{{ route('staff.pdp.plans.reviews.open', [$plan, $review->period_key]) }}">
                                                    @csrf
                                                    @include('pdp.partials.submit-button', [
                                                        'label' => 'Open Review',
                                                        'loadingText' => 'Opening review...',
                                                        'icon' => 'bx bx-play-circle',
                                                        'variant' => 'btn-outline-primary',
                                                    ])
                                                </form>
                                            @elseif ($review->status === \App\Models\Pdp\PdpPlanReview::STATUS_OPEN)
                                                <form method="POST" action="{{ route('staff.pdp.plans.reviews.close', [$plan, $review->period_key]) }}">
                                                    @csrf
                                                    @include('pdp.partials.submit-button', [
                                                        'label' => 'Close and Score',
                                                        'loadingText' => 'Scoring review...',
                                                        'icon' => 'bx bx-check-double',
                                                        'variant' => 'btn-primary',
                                                    ])
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @foreach ($sections as $sectionData)
            @include($viewService->sectionPartial($sectionData['section']), [
                'plan' => $plan,
                'sectionData' => $sectionData,
                'viewService' => $viewService,
            ])
        @endforeach

        @include('pdp.sections.signature-block', [
            'plan' => $plan,
            'sectionData' => [
                'section' => (object) ['key' => 'approvals', 'label' => 'Approvals and Signatures'],
            ],
            'viewService' => $viewService,
            'reviewService' => $reviewService,
        ])
    </div>
@endsection
@section('script')
    @include('pdp.partials.theme-script')
@endsection

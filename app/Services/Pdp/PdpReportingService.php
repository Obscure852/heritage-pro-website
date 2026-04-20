<?php

namespace App\Services\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpPlanReview;
use App\Models\Pdp\PdpPlanSignature;
use Illuminate\Support\Collection;

class PdpReportingService
{
    public function buildDashboard(): array
    {
        $plans = PdpPlan::query()
            ->with([
                'template',
                'user',
                'supervisor',
                'reviews',
                'signatures.review',
            ])
            ->orderByDesc('created_at')
            ->get();

        $plansByStatus = collect([
            PdpPlan::STATUS_DRAFT,
            PdpPlan::STATUS_ACTIVE,
            PdpPlan::STATUS_COMPLETED,
            PdpPlan::STATUS_CANCELLED,
        ])->map(fn (string $status): array => [
            'status' => $status,
            'count' => $plans->where('status', $status)->count(),
        ]);

        $plansByTemplate = $plans
            ->groupBy('pdp_template_id')
            ->map(function (Collection $group): array {
                /** @var \App\Models\Pdp\PdpPlan $first */
                $first = $group->first();

                return [
                    'template' => $first->template,
                    'count' => $group->count(),
                    'active_count' => $group->where('status', PdpPlan::STATUS_ACTIVE)->count(),
                    'completed_count' => $group->where('status', PdpPlan::STATUS_COMPLETED)->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        $reviewBacklog = $plans
            ->flatMap(fn (PdpPlan $plan): Collection => $plan->reviews->map(fn (PdpPlanReview $review): array => [
                'plan' => $plan,
                'review' => $review,
            ]))
            ->filter(fn (array $row): bool => in_array($row['review']->status, [
                PdpPlanReview::STATUS_PENDING,
                PdpPlanReview::STATUS_OPEN,
            ], true))
            ->values();

        $signatureBacklog = $plans
            ->flatMap(fn (PdpPlan $plan): Collection => $plan->signatures->map(fn (PdpPlanSignature $signature): array => [
                'plan' => $plan,
                'signature' => $signature,
            ]))
            ->filter(fn (array $row): bool => $row['signature']->status === PdpPlanSignature::STATUS_PENDING)
            ->values();

        return [
            'metrics' => [
                ['label' => 'Total Plans', 'value' => $plans->count(), 'tone' => 'primary'],
                ['label' => 'Active Plans', 'value' => $plans->where('status', PdpPlan::STATUS_ACTIVE)->count(), 'tone' => 'success'],
                ['label' => 'Completed Plans', 'value' => $plans->where('status', PdpPlan::STATUS_COMPLETED)->count(), 'tone' => 'dark'],
                ['label' => 'Pending Signatures', 'value' => $signatureBacklog->count(), 'tone' => 'warning'],
            ],
            'plansByStatus' => $plansByStatus,
            'plansByTemplate' => $plansByTemplate,
            'reviewBacklog' => $reviewBacklog,
            'signatureBacklog' => $signatureBacklog,
            'recentPlans' => $plans->take(10),
        ];
    }
}

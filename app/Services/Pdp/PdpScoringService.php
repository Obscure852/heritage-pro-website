<?php

namespace App\Services\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpPlanReview;
use App\Models\Pdp\PdpTemplateField;
use App\Models\Pdp\PdpTemplateRatingScheme;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class PdpScoringService
{
    public function calculateReviewSummary(PdpPlan $plan, PdpPlanReview|string $review): array
    {
        $plan = $plan->fresh([
            'template.sections.fields',
            'template.ratingSchemes',
            'template.periods',
            'reviews',
            'sectionEntries',
        ]);

        $review = is_string($review)
            ? $plan->reviews->firstWhere('period_key', $review)
            : $plan->reviews->firstWhere('id', $review->id);

        if (!$review) {
            throw new InvalidArgumentException('Unable to calculate scores for an unknown PDP review.');
        }

        $componentScores = [];

        foreach ($plan->template->ratingSchemes as $scheme) {
            $values = $this->fieldValuesForScheme($plan, $review, $scheme);
            if ($values->isEmpty()) {
                continue;
            }

            $rawAverage = $values->avg();
            $convertedAverage = $values
                ->map(fn ($value) => $this->convertToPercentage($scheme, $value))
                ->avg();

            $weightedScore = $scheme->weight !== null
                ? $convertedAverage * (float) $scheme->weight
                : $convertedAverage;

            $componentScores[$scheme->key] = [
                'label' => $scheme->label,
                'input_type' => $scheme->input_type,
                'weight' => $scheme->weight !== null ? (float) $scheme->weight : null,
                'raw_average' => $this->applyRounding($rawAverage, $scheme->rounding_rule),
                'converted_average' => $this->applyRounding($convertedAverage, $scheme->rounding_rule),
                'weighted_score' => $this->applyRounding($weightedScore, $scheme->rounding_rule),
                'count' => $values->count(),
            ];
        }

        $totalScore = collect($componentScores)->sum('weighted_score');
        $ratingBand = $this->lookupBand($plan, $totalScore);
        $mappedSummary = $this->mappedReviewSummary($plan, $review, $componentScores, $totalScore, $ratingBand);

        return [
            'period_key' => $review->period_key,
            'component_scores' => $componentScores,
            'total_score' => $this->applyRounding($totalScore, 'round_2'),
            'rating_band' => $ratingBand,
            'mapped_summary' => $mappedSummary,
            'calculated_at' => now()->toIso8601String(),
        ];
    }

    public function calculatePlanSummary(PdpPlan $plan): array
    {
        $plan = $plan->fresh([
            'template.periods',
            'template.ratingSchemes',
            'reviews',
        ]);

        $closedReviews = $plan->reviews
            ->where('status', PdpPlanReview::STATUS_CLOSED)
            ->filter(fn (PdpPlanReview $review): bool => is_array($review->score_summary_json))
            ->values();

        $summary = [];

        foreach ($closedReviews as $review) {
            foreach (($review->score_summary_json['mapped_summary'] ?? []) as $key => $value) {
                $summary[$key] = $value;
            }
        }

        $includedPeriods = $plan->template->periods
            ->where('include_in_final_score', true)
            ->pluck('key')
            ->all();

        $includedReviews = $closedReviews->filter(fn (PdpPlanReview $review): bool => in_array($review->period_key, $includedPeriods, true))->values();
        if ($includedReviews->isEmpty()) {
            $includedReviews = $closedReviews;
        }

        $finalTotal = $includedReviews->isEmpty()
            ? null
            : $includedReviews->avg(fn (PdpPlanReview $review) => data_get($review->score_summary_json, 'total_score'));

        $finalBand = $finalTotal === null ? null : $this->lookupBand($plan, $finalTotal);

        if ($finalTotal !== null) {
            $summary['summary.final_rating'] = $this->applyRounding($finalTotal, 'round_2');
            $summary['summary.final_rating_band'] = $finalBand;
            $summary['summary.quarterly_total'] = $this->applyRounding(
                (float) data_get($includedReviews->last()?->score_summary_json, 'total_score', $finalTotal),
                'round_2'
            );
        }

        return [
            'summary' => $summary,
            'final_total' => $finalTotal !== null ? $this->applyRounding($finalTotal, 'round_2') : null,
            'final_rating_band' => $finalBand,
            'included_periods' => $includedReviews->pluck('period_key')->all(),
        ];
    }

    private function fieldValuesForScheme(PdpPlan $plan, PdpPlanReview $review, PdpTemplateRatingScheme $scheme): Collection
    {
        $fields = $plan->template->sections
            ->flatMap(fn ($section) => $section->fields)
            ->filter(fn (PdpTemplateField $field): bool => $field->rating_scheme_key === $scheme->key)
            ->filter(function (PdpTemplateField $field) use ($review): bool {
                return $field->period_scope === null || $field->period_scope === $review->period_key;
            });

        return $fields->flatMap(function (PdpTemplateField $field) use ($plan, $review): Collection {
            return $plan->sectionEntries
                ->where('section_key', $field->section->key)
                ->filter(function ($entry) use ($review): bool {
                    return $entry->pdp_plan_review_id === null || $entry->pdp_plan_review_id === $review->id;
                })
                ->map(fn ($entry) => data_get($entry->values_json, $field->key))
                ->filter(fn ($value) => is_numeric($value));
        })->values();
    }

    private function convertToPercentage(PdpTemplateRatingScheme $scheme, float|int|string $value): float
    {
        $numericValue = (float) $value;

        return match ($scheme->input_type) {
            'direct_percentage' => $numericValue,
            'intensity_scale', 'band_scale' => $this->scaledPercentage($scheme, $numericValue),
            default => $numericValue,
        };
    }

    private function scaledPercentage(PdpTemplateRatingScheme $scheme, float $value): float
    {
        $scaleMax = data_get($scheme->scale_config_json, 'max');
        $scaleMin = data_get($scheme->scale_config_json, 'min', 0);

        if ($scaleMax === null) {
            $bandValues = collect($scheme->band_config_json ?? [])->pluck('value')->filter(fn ($bandValue) => is_numeric($bandValue));
            $scaleMax = $bandValues->max() ?: 100;
            $scaleMin = $bandValues->min() ?: 0;
        }

        $conversionType = data_get($scheme->conversion_config_json, 'type');
        if ($conversionType === 'rating_to_percentage' && $scaleMax > 0) {
            return ($value / $scaleMax) * 100;
        }

        $range = max(1, (float) $scaleMax - (float) $scaleMin);

        return (($value - (float) $scaleMin) / $range) * 100;
    }

    private function lookupBand(PdpPlan $plan, float $score): ?string
    {
        $bandScheme = $plan->template->ratingSchemes->first(fn (PdpTemplateRatingScheme $scheme): bool => $scheme->input_type === 'band_lookup');

        if (!$bandScheme) {
            return null;
        }

        foreach ($bandScheme->band_config_json ?? [] as $band) {
            $min = (float) ($band['min'] ?? 0);
            $max = (float) ($band['max'] ?? 0);

            if ($score >= $min && $score <= $max) {
                return $band['label'] ?? null;
            }
        }

        return null;
    }

    private function mappedReviewSummary(PdpPlan $plan, PdpPlanReview $review, array $componentScores, float $totalScore, ?string $ratingBand): array
    {
        $periodKey = $review->period_key;
        $summary = [
            "summary.{$periodKey}_total" => $this->applyRounding($totalScore, 'round_2'),
        ];

        $weightedScores = collect($componentScores)->mapWithKeys(fn (array $component, string $schemeKey): array => [$schemeKey => $component['weighted_score']]);

        if (str_contains($plan->template->code, 'school')) {
            $summary["summary.{$periodKey}_performance"] = $weightedScores->get('performance_percentage');

            if ($periodKey === 'mid_year') {
                $summary['summary.mid_year_attributes'] = $weightedScores->get('behaviour_intensity');
                $summary['summary.mid_year_total'] = $this->applyRounding($totalScore, 'round_2');
            }

            if ($periodKey === 'year_end') {
                $summary['summary.year_end_total'] = $this->applyRounding($totalScore, 'round_2');
                $summary['summary.final_rating_band'] = $ratingBand;
            }
        }

        if (str_contains($plan->template->code, 'dpsm')) {
            $summary['summary.quarterly_total'] = $this->applyRounding($totalScore, 'round_2');
            $summary['summary.final_rating'] = $this->applyRounding($totalScore, 'round_2');
            $summary['summary.final_rating_band'] = $ratingBand;
        }

        if ($ratingBand !== null) {
            $summary['summary.final_rating_band'] = $ratingBand;
        }

        return $summary;
    }

    private function applyRounding(float|int|null $value, ?string $rule): ?float
    {
        if ($value === null) {
            return null;
        }

        return match ($rule) {
            'round_0' => round((float) $value, 0),
            'round_1' => round((float) $value, 1),
            default => round((float) $value, 2),
        };
    }
}

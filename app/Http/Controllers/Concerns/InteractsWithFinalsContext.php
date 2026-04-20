<?php

namespace App\Http\Controllers\Concerns;

use App\Services\Finals\FinalsContextDefinition;
use App\Services\Finals\FinalsQueryService;
use App\Services\SchoolModeResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

trait InteractsWithFinalsContext
{
    protected function schoolModeResolver(): SchoolModeResolver
    {
        return app(SchoolModeResolver::class);
    }

    protected function finalsDefinition(?Request $request = null, ?string $context = null): FinalsContextDefinition
    {
        $requestContext = $context;
        if ($requestContext === null && $request !== null) {
            $requestContext = $request->query('finals_context');
        }

        return $this->schoolModeResolver()->finalsDefinition($requestContext);
    }

    protected function finalsContext(?Request $request = null, ?string $context = null): string
    {
        $requestContext = $context;
        if ($requestContext === null && $request !== null) {
            $requestContext = $request->query('finals_context');
        }

        return $this->schoolModeResolver()->currentFinalsContext($requestContext);
    }

    protected function finalsQueryService(): FinalsQueryService
    {
        return app(FinalsQueryService::class);
    }

    /**
     * @return array<string, string>
     */
    protected function finalsQueryParameters(?Request $request = null, ?FinalsContextDefinition $definition = null): array
    {
        return [
            'finals_context' => $definition?->context ?? $this->finalsContext($request),
        ];
    }

    /**
     * @param  array<string, mixed>  $replacements
     * @return array<int, array<string, mixed>>
     */
    protected function finalsReportMenu(FinalsContextDefinition $definition, string $section, array $replacements = []): array
    {
        return collect($definition->reportMenu[$section] ?? [])
            ->map(function (array $item) use ($definition, $replacements) {
                $params = [];
                $enabled = true;

                foreach (($item['params'] ?? []) as $key => $value) {
                    if (is_string($value) && str_starts_with($value, '__') && str_ends_with($value, '__')) {
                        $replacementKey = strtolower(trim($value, '_'));
                        $replacementValue = $replacements[$replacementKey] ?? null;

                        if ($replacementValue === null || $replacementValue === '') {
                            $enabled = false;
                            continue;
                        }

                        $params[$key] = $replacementValue;
                        continue;
                    }

                    $params[$key] = $value;
                }

                $params = array_merge($params, $this->finalsQueryParameters(definition: $definition));

                $item['enabled'] = $enabled;
                $item['url'] = $enabled ? route($item['route'], $params) : null;

                return $item;
            })
            ->values()
            ->all();
    }

    protected function scopeFinalsQuery(Builder|Relation $query, string $entity, FinalsContextDefinition $definition): Builder|Relation
    {
        return match ($entity) {
            'final_students' => $this->finalsQueryService()->applyToFinalStudents($query, $definition),
            'final_klasses' => $this->finalsQueryService()->applyToFinalKlasses($query, $definition),
            'final_grade_subjects' => $this->finalsQueryService()->applyToFinalGradeSubjects($query, $definition),
            'final_klass_subjects' => $this->finalsQueryService()->applyToFinalKlassSubjects($query, $definition),
            'final_optional_subjects' => $this->finalsQueryService()->applyToFinalOptionalSubjects($query, $definition),
            'final_houses' => $this->finalsQueryService()->applyToFinalHouses($query, $definition),
            'external_exam_results' => $this->finalsQueryService()->applyToExternalExamResults($query, $definition),
            default => $query,
        };
    }
}

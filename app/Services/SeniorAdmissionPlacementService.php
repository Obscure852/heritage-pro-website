<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Klass;
use App\Models\SchoolSetup;
use App\Models\SeniorAdmissionAcademic;
use App\Models\SeniorAdmissionPlacementCriteria;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SeniorAdmissionPlacementService
{
    public const GRADE_ORDER = [
        'A' => 1,
        'B' => 2,
        'C' => 3,
        'D' => 4,
        'E' => 5,
        'U' => 6,
    ];

    public const OVERALL_GRADE_ORDER = [
        'A' => 1,
        'B' => 2,
        'C' => 3,
        'D' => 4,
        'M' => 5,
    ];

    public const LABELS = [
        SeniorAdmissionPlacementCriteria::PATHWAY_TRIPLE => 'Triple Science',
        SeniorAdmissionPlacementCriteria::PATHWAY_DOUBLE => 'Double Science',
        SeniorAdmissionPlacementCriteria::PATHWAY_SINGLE => 'Single Science Award',
        'unclassified' => 'Unclassified',
    ];

    public const CLASS_TYPES = [
        SeniorAdmissionPlacementCriteria::PATHWAY_TRIPLE => Klass::TYPE_TRIPLE_AWARD,
        SeniorAdmissionPlacementCriteria::PATHWAY_DOUBLE => Klass::TYPE_DOUBLE_AWARD,
        SeniorAdmissionPlacementCriteria::PATHWAY_SINGLE => Klass::TYPE_SINGLE_AWARD,
    ];

    public function criteriaForSchool(?SchoolSetup $schoolSetup = null): Collection
    {
        $schoolSetup ??= SchoolSetup::current();

        $defaults = $this->defaultCriteria($schoolSetup?->id);

        if (!$schoolSetup) {
            return $defaults;
        }

        $existing = SeniorAdmissionPlacementCriteria::query()
            ->where('school_setup_id', $schoolSetup->id)
            ->orderBy('priority')
            ->get()
            ->keyBy('pathway');

        return collect(SeniorAdmissionPlacementCriteria::PATHWAYS)
            ->map(function (string $pathway) use ($existing, $defaults) {
                if ($existing->has($pathway)) {
                    return $this->normalizeCriteriaRow($existing->get($pathway)->toArray());
                }

                return $defaults->firstWhere('pathway', $pathway);
            })
            ->values();
    }

    public function persistCriteria(SchoolSetup $schoolSetup, array $criteria): Collection
    {
        return DB::transaction(function () use ($schoolSetup, $criteria) {
            foreach ($this->criteriaForPersistence($schoolSetup->id, $criteria) as $row) {
                SeniorAdmissionPlacementCriteria::query()->updateOrCreate(
                    [
                        'school_setup_id' => $schoolSetup->id,
                        'pathway' => $row['pathway'],
                    ],
                    $row
                );
            }

            return $this->criteriaForSchool($schoolSetup);
        });
    }

    public function resetCriteria(SchoolSetup $schoolSetup): Collection
    {
        return DB::transaction(function () use ($schoolSetup) {
            SeniorAdmissionPlacementCriteria::query()
                ->where('school_setup_id', $schoolSetup->id)
                ->delete();

            foreach ($this->defaultCriteria($schoolSetup->id) as $row) {
                SeniorAdmissionPlacementCriteria::query()->create($row);
            }

            return $this->criteriaForSchool($schoolSetup);
        });
    }

    public function recommendForAdmission(Admission $admission, ?Collection $criteria = null): array
    {
        $academic = $admission->relationLoaded('seniorAdmissionAcademic')
            ? $admission->seniorAdmissionAcademic
            : $admission->seniorAdmissionAcademic()->first();

        return $this->recommendForAcademic($academic, $criteria);
    }

    public function recommendForAcademic(?SeniorAdmissionAcademic $academic, ?Collection $criteria = null): array
    {
        $criteria ??= $this->criteriaForSchool();
        $activeCriteria = $criteria
            ->filter(fn(array $row) => $row['is_active'])
            ->sortBy('priority')
            ->values();

        $resolved = $this->resolveEffectiveScienceGrade($academic);
        $scienceGrade = $resolved['grade'];
        $scienceSubject = $resolved['subject'];
        $mathematicsGrade = strtoupper((string) data_get($academic, 'mathematics', ''));

        if ($scienceGrade === '' || $mathematicsGrade === '') {
            return $this->formatRecommendation('unclassified', null, 'Science and Mathematics grades are required before recommending a science pathway.');
        }

        foreach ($activeCriteria as $row) {
            if ($row['pathway'] === SeniorAdmissionPlacementCriteria::PATHWAY_SINGLE) {
                continue;
            }

            if (
                $this->gradeMatchesBand($scienceGrade, $row['science_best_grade'], $row['science_worst_grade'])
                && $this->gradeMatchesBand($mathematicsGrade, $row['mathematics_best_grade'], $row['mathematics_worst_grade'])
            ) {
                return $this->formatRecommendation(
                    $row['pathway'],
                    $this->classTypeForPathway($row['pathway']),
                    sprintf('Based on %s %s and Mathematics %s.', $scienceSubject, $scienceGrade, $mathematicsGrade),
                    $scienceSubject
                );
            }
        }

        $fallback = $this->formatRecommendation(
            SeniorAdmissionPlacementCriteria::PATHWAY_SINGLE,
            $this->classTypeForPathway(SeniorAdmissionPlacementCriteria::PATHWAY_SINGLE),
            sprintf('Fallback pathway for %s %s and Mathematics %s.', $scienceSubject, $scienceGrade, $mathematicsGrade),
            $scienceSubject
        );

        $fallbackCriteria = $activeCriteria->firstWhere('pathway', $fallback['pathway']);
        if ($fallbackCriteria) {
            $ceiling = $this->normalizeGrade($fallbackCriteria['science_ceiling_grade'] ?? null);
            $promoteTo = $fallbackCriteria['promotion_pathway'] ?? null;

            if ($ceiling && $promoteTo && $this->gradeIsBetterThan($scienceGrade, $ceiling)) {
                return $this->formatRecommendation(
                    $promoteTo,
                    $this->classTypeForPathway($promoteTo),
                    sprintf('Promoted from %s to %s based on %s grade %s.', $this->labelForPathway($fallback['pathway']), $this->labelForPathway($promoteTo), $scienceSubject, $scienceGrade),
                    $scienceSubject
                );
            }
        }

        return $fallback;
    }

    public function autoDistributeToClasses(Collection $students, Collection $classes): array
    {
        $availableClasses = $classes
            ->sortBy('name')
            ->values();

        if ($availableClasses->isEmpty()) {
            return [
                'mapping' => [],
                'capacity_warnings' => [],
            ];
        }

        $mapping = [];
        $capacityWarnings = [];
        $classCount = $availableClasses->count();
        $capacityUsed = [];

        foreach ($availableClasses as $index => $klass) {
            $capacityUsed[$index] = $klass->students_count ?? 0;
        }

        $forward = true;
        $classIndex = 0;

        foreach ($students as $row) {
            $admissionId = data_get($row, 'admission.id') ?? data_get($row, 'admission_id');
            if (!$admissionId) {
                continue;
            }

            $placed = false;
            $attempts = 0;

            while (!$placed && $attempts < $classCount) {
                $klass = $availableClasses->get($classIndex);
                $maxStudents = $klass->max_students;
                $hasRemainingCapacity = collect($capacityUsed)->contains(function ($count, $index) use ($availableClasses) {
                    $class = $availableClasses->get($index);
                    $max = $class->max_students;

                    return $max === null || $count < $max;
                });
                $canUseClass = !$hasRemainingCapacity || $maxStudents === null || $capacityUsed[$classIndex] < $maxStudents;

                if ($canUseClass) {
                    $isAtOrOverCapacity = $maxStudents !== null && $capacityUsed[$classIndex] >= $maxStudents;
                    $mapping[$admissionId] = $klass->id;
                    if ($isAtOrOverCapacity) {
                        $capacityWarnings[$admissionId] = $klass->id;
                    }
                    $capacityUsed[$classIndex]++;
                    $placed = true;
                }

                if ($forward) {
                    $classIndex++;
                    if ($classIndex >= $classCount) {
                        $classIndex = $classCount - 1;
                        $forward = false;
                    }
                } else {
                    $classIndex--;
                    if ($classIndex < 0) {
                        $classIndex = 0;
                        $forward = true;
                    }
                }

                $attempts++;
            }
        }

        return [
            'mapping' => $mapping,
            'capacity_warnings' => $capacityWarnings,
        ];
    }

    public function gradeIsBetterThan(string $grade, string $threshold): bool
    {
        $gradeRank = self::GRADE_ORDER[strtoupper($grade)] ?? null;
        $thresholdRank = self::GRADE_ORDER[strtoupper($threshold)] ?? null;

        if ($gradeRank === null || $thresholdRank === null) {
            return false;
        }

        return $gradeRank < $thresholdRank;
    }

    public function splitClassesByRecommendation(iterable $classes, array $recommendation): array
    {
        $classes = collect($classes)->values();
        $classType = $recommendation['class_type'] ?? null;

        if (!$classType) {
            return [
                'recommended' => $classes,
                'alternatives' => collect(),
                'has_exact_match' => false,
            ];
        }

        $recommended = $classes->filter(fn($class) => $class->type === $classType)->values();
        $alternatives = $classes->reject(fn($class) => $class->type === $classType)->values();

        if ($recommended->isEmpty()) {
            return [
                'recommended' => $classes,
                'alternatives' => collect(),
                'has_exact_match' => false,
            ];
        }

        return [
            'recommended' => $recommended,
            'alternatives' => $alternatives,
            'has_exact_match' => true,
        ];
    }

    public function buildTermSummary(int $termId, ?SchoolSetup $schoolSetup = null): array
    {
        $criteria = $this->criteriaForSchool($schoolSetup);
        $counts = [
            SeniorAdmissionPlacementCriteria::PATHWAY_TRIPLE => 0,
            SeniorAdmissionPlacementCriteria::PATHWAY_DOUBLE => 0,
            SeniorAdmissionPlacementCriteria::PATHWAY_SINGLE => 0,
            'unclassified' => 0,
        ];

        Admission::query()
            ->where('term_id', $termId)
            ->where('grade_applying_for', 'F4')
            ->with('seniorAdmissionAcademic')
            ->get()
            ->each(function (Admission $admission) use (&$counts, $criteria) {
                $recommendation = $this->recommendForAdmission($admission, $criteria);
                $counts[$recommendation['pathway']] = ($counts[$recommendation['pathway']] ?? 0) + 1;
            });

        return $criteria
            ->map(function (array $row) use ($counts) {
                $current = $counts[$row['pathway']] ?? 0;

                return [
                    'pathway' => $row['pathway'],
                    'label' => $row['label'],
                    'target_count' => $row['target_count'],
                    'current_count' => $current,
                    'difference' => $row['target_count'] - $current,
                ];
            })
            ->push([
                'pathway' => 'unclassified',
                'label' => self::LABELS['unclassified'],
                'target_count' => null,
                'current_count' => $counts['unclassified'],
                'difference' => null,
            ])
            ->all();
    }

    public function buildPlacementGroups(int $termId, ?SchoolSetup $schoolSetup = null, ?Collection $availableClassTypes = null): array
    {
        $criteria = $this->criteriaForSchool($schoolSetup)->keyBy('pathway');

        $includeSingle = $availableClassTypes === null
            || $availableClassTypes->isEmpty()
            || $availableClassTypes->contains(Klass::TYPE_SINGLE_AWARD);

        $groupDefinitions = collect([
            SeniorAdmissionPlacementCriteria::PATHWAY_TRIPLE,
            SeniorAdmissionPlacementCriteria::PATHWAY_DOUBLE,
            $includeSingle ? SeniorAdmissionPlacementCriteria::PATHWAY_SINGLE : null,
            'unclassified',
        ])->filter()->values()->all();

        $groups = collect($groupDefinitions)->mapWithKeys(function (string $pathway) use ($criteria) {
            $criteriaRow = $criteria->get($pathway);

            return [$pathway => [
                'pathway' => $pathway,
                'label' => self::LABELS[$pathway] ?? ucfirst($pathway),
                'class_type' => $criteriaRow['class_type'] ?? $this->classTypeForPathway($pathway),
                'target_count' => $pathway === 'unclassified' ? null : (int) ($criteriaRow['target_count'] ?? 0),
                'students' => collect(),
                'count' => 0,
                'selected_count' => 0,
            ]];
        });

        Admission::query()
            ->where('term_id', $termId)
            ->where('grade_applying_for', 'F4')
            ->whereNotIn('status', ['Enrolled', 'Deleted'])
            ->with('seniorAdmissionAcademic')
            ->get()
            ->each(function (Admission $admission) use (&$groups, $criteria, $includeSingle) {
                $recommendation = $this->recommendForAdmission($admission, $criteria->values());
                $pathway = $recommendation['pathway'];
                $academic = $admission->seniorAdmissionAcademic;

                // When Single pathway is excluded, redirect those students to Double
                if (!$includeSingle && $pathway === SeniorAdmissionPlacementCriteria::PATHWAY_SINGLE) {
                    $pathway = SeniorAdmissionPlacementCriteria::PATHWAY_DOUBLE;
                }

                $resolved = $this->resolveEffectiveScienceGrade($academic);

                $group = $groups->get($pathway);
                $group['students']->push([
                    'admission' => $admission,
                    'recommendation' => $recommendation,
                    'science' => $resolved['grade'] ?: data_get($academic, 'science'),
                    'science_subject' => $resolved['subject'],
                    'mathematics' => data_get($academic, 'mathematics'),
                    'overall' => data_get($academic, 'overall'),
                ]);
                $groups->put($pathway, $group);
            });

        return $groups
            ->map(function (array $group) {
                $students = $group['students']
                    ->sort(fn(array $left, array $right) => $this->comparePlacementRows($left, $right))
                    ->values()
                    ->map(function (array $row, int $index) use ($group) {
                        $isSelectable = $group['pathway'] !== 'unclassified';
                        $row['rank'] = $index + 1;
                        $row['auto_selected'] = $isSelectable
                            && $group['target_count'] !== null
                            && $index < $group['target_count'];

                        return $row;
                    });

                $group['students'] = $students;
                $group['count'] = $students->count();
                $group['selected_count'] = $students->where('auto_selected', true)->count();

                return $group;
            })
            ->values()
            ->all();
    }

    public function defaultCriteria(?int $schoolSetupId = null): Collection
    {
        return collect([
            [
                'school_setup_id' => $schoolSetupId,
                'pathway' => SeniorAdmissionPlacementCriteria::PATHWAY_TRIPLE,
                'priority' => 1,
                'science_best_grade' => 'A',
                'science_worst_grade' => 'B',
                'mathematics_best_grade' => 'A',
                'mathematics_worst_grade' => 'B',
                'science_ceiling_grade' => null,
                'promotion_pathway' => null,
                'target_count' => 0,
                'is_active' => true,
            ],
            [
                'school_setup_id' => $schoolSetupId,
                'pathway' => SeniorAdmissionPlacementCriteria::PATHWAY_DOUBLE,
                'priority' => 2,
                'science_best_grade' => 'C',
                'science_worst_grade' => 'D',
                'mathematics_best_grade' => 'C',
                'mathematics_worst_grade' => 'D',
                'science_ceiling_grade' => null,
                'promotion_pathway' => null,
                'target_count' => 0,
                'is_active' => true,
            ],
            [
                'school_setup_id' => $schoolSetupId,
                'pathway' => SeniorAdmissionPlacementCriteria::PATHWAY_SINGLE,
                'priority' => 3,
                'science_best_grade' => null,
                'science_worst_grade' => null,
                'mathematics_best_grade' => null,
                'mathematics_worst_grade' => null,
                'science_ceiling_grade' => null,
                'promotion_pathway' => null,
                'target_count' => 0,
                'is_active' => true,
            ],
        ])->map(fn(array $row) => $this->normalizeCriteriaRow($row));
    }

    public function gradeMatchesBand(?string $grade, ?string $bestGrade, ?string $worstGrade): bool
    {
        if (!$grade || !$bestGrade || !$worstGrade) {
            return false;
        }

        $gradeRank = self::GRADE_ORDER[$grade] ?? null;
        $bestRank = self::GRADE_ORDER[$bestGrade] ?? null;
        $worstRank = self::GRADE_ORDER[$worstGrade] ?? null;

        if ($gradeRank === null || $bestRank === null || $worstRank === null) {
            return false;
        }

        return $gradeRank >= $bestRank && $gradeRank <= $worstRank;
    }

    public function isBandOrdered(?string $bestGrade, ?string $worstGrade): bool
    {
        if (!$bestGrade && !$worstGrade) {
            return true;
        }

        if (!$bestGrade || !$worstGrade) {
            return false;
        }

        return (self::GRADE_ORDER[$bestGrade] ?? PHP_INT_MAX) <= (self::GRADE_ORDER[$worstGrade] ?? 0);
    }

    public function classTypeForPathway(string $pathway): ?string
    {
        return self::CLASS_TYPES[$pathway] ?? null;
    }

    public function labelForPathway(string $pathway): string
    {
        return self::LABELS[$pathway] ?? ucfirst($pathway);
    }

    private function criteriaForPersistence(int $schoolSetupId, array $criteria): Collection
    {
        return collect(SeniorAdmissionPlacementCriteria::PATHWAYS)
            ->map(function (string $pathway, int $index) use ($criteria, $schoolSetupId) {
                $row = $criteria[$pathway] ?? [];

                return [
                    'school_setup_id' => $schoolSetupId,
                    'pathway' => $pathway,
                    'priority' => $index + 1,
                    'science_best_grade' => $this->normalizeGrade($row['science_best_grade'] ?? null),
                    'science_worst_grade' => $this->normalizeGrade($row['science_worst_grade'] ?? null),
                    'mathematics_best_grade' => $this->normalizeGrade($row['mathematics_best_grade'] ?? null),
                    'mathematics_worst_grade' => $this->normalizeGrade($row['mathematics_worst_grade'] ?? null),
                    'science_ceiling_grade' => $this->normalizeGrade($row['science_ceiling_grade'] ?? null),
                    'promotion_pathway' => $row['promotion_pathway'] ?? null,
                    'target_count' => (int) ($row['target_count'] ?? 0),
                    'is_active' => (bool) ($row['is_active'] ?? false),
                ];
            });
    }

    private function normalizeCriteriaRow(array $row): array
    {
        $row['pathway'] = $row['pathway'];
        $row['label'] = $this->labelForPathway($row['pathway']);
        $row['class_type'] = $this->classTypeForPathway($row['pathway']);
        $row['priority'] = (int) ($row['priority'] ?? 0);
        $row['science_best_grade'] = $this->normalizeGrade($row['science_best_grade'] ?? null);
        $row['science_worst_grade'] = $this->normalizeGrade($row['science_worst_grade'] ?? null);
        $row['mathematics_best_grade'] = $this->normalizeGrade($row['mathematics_best_grade'] ?? null);
        $row['mathematics_worst_grade'] = $this->normalizeGrade($row['mathematics_worst_grade'] ?? null);
        $row['science_ceiling_grade'] = $this->normalizeGrade($row['science_ceiling_grade'] ?? null);
        $row['promotion_pathway'] = $row['promotion_pathway'] ?? null;
        $row['target_count'] = (int) ($row['target_count'] ?? 0);
        $row['is_active'] = (bool) ($row['is_active'] ?? false);

        return $row;
    }

    private function normalizeGrade(?string $grade): ?string
    {
        $grade = strtoupper(trim((string) $grade));

        return $grade === '' ? null : $grade;
    }

    private function comparePlacementRows(array $left, array $right): int
    {
        $comparisons = [
            $this->subjectRank($left['science'] ?? null) <=> $this->subjectRank($right['science'] ?? null),
            $this->subjectRank($left['mathematics'] ?? null) <=> $this->subjectRank($right['mathematics'] ?? null),
            $this->overallRank($left['overall'] ?? null) <=> $this->overallRank($right['overall'] ?? null),
            strcasecmp((string) data_get($left, 'admission.last_name'), (string) data_get($right, 'admission.last_name')),
            strcasecmp((string) data_get($left, 'admission.first_name'), (string) data_get($right, 'admission.first_name')),
            ((int) data_get($left, 'admission.id')) <=> ((int) data_get($right, 'admission.id')),
        ];

        foreach ($comparisons as $comparison) {
            if ($comparison !== 0) {
                return $comparison;
            }
        }

        return 0;
    }

    private function subjectRank(?string $grade): int
    {
        return self::GRADE_ORDER[strtoupper((string) $grade)] ?? 99;
    }

    private function overallRank(?string $grade): int
    {
        return self::OVERALL_GRADE_ORDER[strtoupper((string) $grade)] ?? 99;
    }

    private function resolveEffectiveScienceGrade(?SeniorAdmissionAcademic $academic): array
    {
        $science = strtoupper(trim((string) data_get($academic, 'science', '')));
        if ($science !== '') {
            return ['grade' => $science, 'subject' => 'Science'];
        }

        $privateAgriculture = strtoupper(trim((string) data_get($academic, 'private_agriculture', '')));
        if ($privateAgriculture !== '') {
            return ['grade' => $privateAgriculture, 'subject' => 'Private Agriculture'];
        }

        return ['grade' => '', 'subject' => null];
    }

    private function formatRecommendation(string $pathway, ?string $classType, string $reason, ?string $scienceSubject = null): array
    {
        return [
            'pathway' => $pathway,
            'label' => $this->labelForPathway($pathway),
            'class_type' => $classType,
            'reason' => $reason,
            'science_subject' => $scienceSubject,
            'badge_class' => match ($pathway) {
                SeniorAdmissionPlacementCriteria::PATHWAY_TRIPLE => 'bg-primary-subtle text-primary',
                SeniorAdmissionPlacementCriteria::PATHWAY_DOUBLE => 'bg-warning-subtle text-warning',
                SeniorAdmissionPlacementCriteria::PATHWAY_SINGLE => 'bg-success-subtle text-success',
                default => 'bg-secondary-subtle text-secondary',
            },
        ];
    }
}

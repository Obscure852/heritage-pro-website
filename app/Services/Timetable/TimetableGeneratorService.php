<?php

namespace App\Services\Timetable;

use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableAuditLog;
use App\Models\Timetable\TimetableSetting;
use App\Models\Timetable\TimetableSlot;
use App\Services\Timetable\Generation\Chromosome;
use App\Services\Timetable\Generation\FitnessEvaluator;
use App\Services\Timetable\Generation\Gene;
use App\Services\Timetable\Generation\GenerationData;
use App\Services\Timetable\Generation\GenerationResult;
use App\Services\Timetable\Support\BlockPlacementRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Core genetic algorithm engine for timetable generation.
 *
 * Evolves a population of chromosomes toward an optimal timetable solution
 * that satisfies hard constraints (no double-bookings) and optimises soft
 * constraints (teacher preferences, subject spread, etc.).
 */
class TimetableGeneratorService {
    // GA parameters
    private int $populationSize = 100;
    private int $maxGenerations = 500;
    private float $mutationRate = 0.05;
    private float $crossoverRate = 0.8;
    private int $tournamentSize = 5;
    private int $eliteCount = 2;
    private int $stagnationLimit = 30;
    private float $adaptiveMutationMultiplier = 2.0;
    private float $maxMutationRate = 0.3;
    private int $heuristicTopK = 5;
    private int $repairMoves = 6;
    private bool $settingsApplied = false;

    public const DEFAULT_GA_PARAMETERS = [
        'population_size' => 100,
        'max_generations' => 500,
        'mutation_rate' => 0.05,
        'crossover_rate' => 0.8,
        'tournament_size' => 5,
        'elite_count' => 2,
        'stagnation_limit' => 30,
        'adaptive_mutation_multiplier' => 2.0,
        'max_mutation_rate' => 0.3,
        'heuristic_top_k' => 5,
        'repair_moves' => 6,
    ];

    public const GA_PROFILES = [
        'small' => [
            'label' => 'Small School',
            'description' => 'Up to 200 genes (< 40 teachers)',
            'max_genes' => 200,
            'params' => [
                'population_size' => 80,
                'max_generations' => 400,
                'stagnation_limit' => 25,
                'elite_count' => 2,
                'repair_moves' => 4,
            ],
        ],
        'medium' => [
            'label' => 'Medium School',
            'description' => '200-500 genes (40-80 teachers)',
            'max_genes' => 500,
            'params' => [
                'population_size' => 100,
                'max_generations' => 600,
                'stagnation_limit' => 40,
                'elite_count' => 3,
                'repair_moves' => 6,
            ],
        ],
        'large' => [
            'label' => 'Large School',
            'description' => '500-1000 genes (80-150 teachers)',
            'max_genes' => 1000,
            'params' => [
                'population_size' => 60,
                'max_generations' => 1000,
                'stagnation_limit' => 60,
                'elite_count' => 3,
                'mutation_rate' => 0.08,
                'repair_moves' => 8,
                'heuristic_top_k' => 3,
            ],
        ],
        'very_large' => [
            'label' => 'Very Large School',
            'description' => '1000+ genes (150+ teachers)',
            'max_genes' => PHP_INT_MAX,
            'params' => [
                'population_size' => 40,
                'max_generations' => 1500,
                'stagnation_limit' => 80,
                'elite_count' => 2,
                'mutation_rate' => 0.10,
                'repair_moves' => 10,
                'heuristic_top_k' => 3,
            ],
        ],
    ];

    /**
     * Load saved GA parameters from TimetableSetting and apply to properties.
     *
     * Safe to call multiple times — only loads from DB once. Subsequent calls
     * are no-ops, so properties set via reflection (tests) or direct assignment
     * are not overwritten if applySettings() was already called.
     */
    public function applySettings(): void {
        if ($this->settingsApplied) {
            return;
        }
        $this->settingsApplied = true;

        try {
            $saved = TimetableSetting::get('ga_parameters', []);
        } catch (\Throwable) {
            return; // No DB available (unit tests) — keep current values
        }
        if (empty($saved)) {
            return;
        }

        $map = [
            'population_size' => 'populationSize',
            'max_generations' => 'maxGenerations',
            'mutation_rate' => 'mutationRate',
            'crossover_rate' => 'crossoverRate',
            'tournament_size' => 'tournamentSize',
            'elite_count' => 'eliteCount',
            'stagnation_limit' => 'stagnationLimit',
            'adaptive_mutation_multiplier' => 'adaptiveMutationMultiplier',
            'max_mutation_rate' => 'maxMutationRate',
            'heuristic_top_k' => 'heuristicTopK',
            'repair_moves' => 'repairMoves',
        ];

        foreach ($map as $settingKey => $property) {
            if (!isset($saved[$settingKey])) {
                continue;
            }
            $value = match (true) {
                is_float($this->{$property}) => (float) $saved[$settingKey],
                default => (int) $saved[$settingKey],
            };

            // Bounds clamping to prevent malformed DB values from corrupting the GA
            if (in_array($settingKey, ['mutation_rate', 'crossover_rate', 'max_mutation_rate'], true)) {
                $value = max(0.01, min(1.0, $value));
            } elseif (in_array($settingKey, ['adaptive_mutation_multiplier'], true)) {
                $value = max(1.0, min(5.0, $value));
            } else {
                $value = max(1, $value);
            }

            $this->{$property} = $value;
        }
    }

    /**
     * Recommend a GA profile based on gene count.
     *
     * GA_PROFILES must remain ordered from smallest to largest max_genes
     * for this range-check to work correctly.
     */
    public static function recommendProfile(int $geneCount): string {
        foreach (self::GA_PROFILES as $key => $profile) {
            if ($geneCount <= $profile['max_genes']) {
                return $key;
            }
        }
        return array_key_last(self::GA_PROFILES);
    }

    /**
     * Run the genetic algorithm and return the best solution found.
     *
     * @param GenerationData $data      Pre-loaded timetable data
     * @param callable       $progressCallback Called each generation: fn(int $gen, int $maxGen, float $bestFitness)
     * @return GenerationResult
     */
    public function generate(GenerationData $data, callable $progressCallback, ?callable $cancellationCheck = null): GenerationResult {
        $this->applySettings();

        $evaluator = new FitnessEvaluator($data);

        // Build gene template from allocations + coupling groups
        $template = Chromosome::fromAllocations(
            array_values($data->klassSubjects),
            $data->couplingGroups,
            $data->optionalSubjectMap,
        );
        $context = $this->buildGenerationContext($data, $template);

        // Initialize population
        $population = [];
        for ($i = 0; $i < $this->populationSize; $i++) {
            $chr = clone $template;
            $this->randomizePositions($chr, $data, $context);
            $this->repairChromosome($chr, $data, $context, maxMoves: 2);
            $chr->fitness = $evaluator->evaluate($chr);
            $population[] = $chr;
        }

        // Sort descending by fitness
        usort($population, fn(Chromosome $a, Chromosome $b) => $b->fitness <=> $a->fitness);

        $bestFitness = $population[0]->fitness;
        $stagnationCount = 0;
        $effectiveMutationRate = $this->mutationRate;

        // Main GA loop
        for ($gen = 1; $gen <= $this->maxGenerations; $gen++) {
            $newPopulation = [];

            // Elitism: preserve top N unchanged
            for ($e = 0; $e < $this->eliteCount && $e < count($population); $e++) {
                $newPopulation[] = clone $population[$e];
            }

            // Fill remaining slots
            while (count($newPopulation) < $this->populationSize) {
                $parent1 = $this->tournamentSelect($population);
                $parent2 = $this->tournamentSelect($population);

                // Crossover
                if ((mt_rand() / mt_getrandmax()) < $this->crossoverRate) {
                    [$child1, $child2] = $this->crossover($parent1, $parent2, $context);
                } else {
                    $child1 = clone $parent1;
                    $child2 = clone $parent2;
                }

                // Mutate
                $this->mutate($child1, $data, $effectiveMutationRate, $context);
                $this->mutate($child2, $data, $effectiveMutationRate, $context);

                // Evaluate
                $child1->fitness = $evaluator->evaluate($child1);
                $newPopulation[] = $child1;

                if (count($newPopulation) < $this->populationSize) {
                    $child2->fitness = $evaluator->evaluate($child2);
                    $newPopulation[] = $child2;
                }
            }

            // Memetic refinement: local repair on current elite improves feasibility quickly.
            $localRepairCount = min($this->eliteCount, count($newPopulation));
            for ($i = 0; $i < $localRepairCount; $i++) {
                $this->repairChromosome($newPopulation[$i], $data, $context, $this->repairMoves);
                $newPopulation[$i]->fitness = $evaluator->evaluate($newPopulation[$i]);
            }

            // Sort new population
            usort($newPopulation, fn(Chromosome $a, Chromosome $b) => $b->fitness <=> $a->fitness);
            $population = $newPopulation;

            $currentBest = $population[0]->fitness;
            $progressCallback($gen, $this->maxGenerations, $currentBest);

            // Cancellation requested
            if ($cancellationCheck !== null && $cancellationCheck()) {
                break;
            }

            // Perfect solution found
            if ($currentBest >= 1.0) {
                break;
            }

            // Stagnation detection
            if ($currentBest > $bestFitness) {
                $bestFitness = $currentBest;
                $stagnationCount = 0;
                $effectiveMutationRate = $this->mutationRate;
            } else {
                $stagnationCount++;
                if ($stagnationCount > $this->stagnationLimit) {
                    $effectiveMutationRate = min(
                        $this->maxMutationRate,
                        $effectiveMutationRate * $this->adaptiveMutationMultiplier
                    );
                    $stagnationCount = 0;
                }
            }
        }

        // Post-GA venue conflict resolution: reassign venues where double-booked
        $this->resolveVenueConflicts($population[0], $data, $context);
        $population[0]->fitness = $evaluator->evaluate($population[0]);

        // Build result from best chromosome
        $best = $population[0];
        $violationReport = $evaluator->getViolationReport($best);
        $assignedCount = $best->getTotalGenes() - $best->getUnassignedCount();

        // Build per-gene violation map for partial placement
        $geneViolationMap = [];
        $placedCount = $assignedCount;
        $skippedCount = 0;
        if ($best->hardViolationCount > 0) {
            $geneViolationMap = $evaluator->getGeneViolationMap($best);
            $skippedCount = count($geneViolationMap);
            $placedCount = $assignedCount - $skippedCount;
        }

        return new GenerationResult(
            chromosome: $best,
            generations: min($gen, $this->maxGenerations),
            fitness: $best->fitness,
            totalSlots: $assignedCount,
            hardViolationCount: $best->hardViolationCount,
            violationReport: $violationReport,
            geneViolationMap: $geneViolationMap,
            placedCount: max(0, $placedCount),
            skippedCount: $skippedCount,
        );
    }

    /**
     * Constructive initialization using most-constrained-first ordering.
     *
     * This replaces purely random seeding with a constraint-aware heuristic:
     * 1) place hardest units first (smallest domain, longer blocks),
     * 2) score candidate slots by hard+soft penalties,
     * 3) pick stochastically from top-K for diversity.
     */
    private function randomizePositions(Chromosome $chromosome, GenerationData $data, array $context): void {
        $state = $this->createEmptyPlacementState();
        $unitOrder = $this->orderUnitsByDifficulty($context['units'], $context['candidateCache'], $data, $context);

        foreach ($unitOrder as $unitId) {
            $placement = $this->selectPositionForUnit(
                $chromosome,
                $unitId,
                $data,
                $context,
                $state,
                stochastic: true
            );

            if ($placement === null) {
                $placement = $this->randomFallbackPosition($context['units'][$unitId], $data);
            }

            $this->applyUnitPlacement(
                $chromosome,
                $context['units'][$unitId],
                (int) $placement['day'],
                (int) $placement['period'],
                $state
            );
        }
    }

    /**
     * Tournament selection: pick the best from a random subset.
     */
    private function tournamentSelect(array $population): Chromosome {
        $best = null;
        $popSize = count($population);

        for ($i = 0; $i < $this->tournamentSize; $i++) {
            $candidate = $population[mt_rand(0, $popSize - 1)];
            if ($best === null || $candidate->fitness > $best->fitness) {
                $best = $candidate;
            }
        }

        return clone $best;
    }

    /**
     * Position-based crossover: swap time assignments between corresponding genes.
     *
     * Coupled genes are always swapped atomically (all genes with same couplingKey together).
     *
     * @return Chromosome[] [$child1, $child2]
     */
    private function crossover(Chromosome $parent1, Chromosome $parent2, array $context): array {
        $child1 = clone $parent1;
        $child2 = clone $parent2;
        foreach ($context['units'] as $unit) {
            if (mt_rand(0, 1) === 0) {
                continue;
            }

            foreach ($unit['indices'] as $idx) {
                $tmpDay = $child1->genes[$idx]->dayOfCycle;
                $tmpPeriod = $child1->genes[$idx]->startPeriod;
                $child1->genes[$idx]->dayOfCycle = $child2->genes[$idx]->dayOfCycle;
                $child1->genes[$idx]->startPeriod = $child2->genes[$idx]->startPeriod;
                $child2->genes[$idx]->dayOfCycle = $tmpDay;
                $child2->genes[$idx]->startPeriod = $tmpPeriod;
            }
        }

        return [$child1, $child2];
    }

    /**
     * Mutate a chromosome: randomly reassign gene positions.
     *
     * Coupled genes are always moved together atomically.
     */
    private function mutate(Chromosome $chromosome, GenerationData $data, float $mutationRate, array $context): void {
        $units = $context['units'];
        if (empty($units)) {
            return;
        }

        $state = $this->buildPlacementStateFromChromosome($chromosome, $context);
        $conflicted = $this->collectConflictedUnits($chromosome, $data, $context, $state);

        $conflictedIds = array_keys($conflicted);
        usort(
            $conflictedIds,
            fn(int $a, int $b) => ($conflicted[$b] ?? 0) <=> ($conflicted[$a] ?? 0)
        );

        $allUnitIds = array_keys($units);
        $remainingIds = array_values(array_diff($allUnitIds, $conflictedIds));
        shuffle($remainingIds);
        $mutationOrder = array_merge($conflictedIds, $remainingIds);

        $mutationBudget = max(1, (int) ceil(count($units) * $mutationRate));
        $mutated = 0;

        foreach ($mutationOrder as $unitId) {
            if ($mutated >= $mutationBudget) {
                break;
            }

            $isConflicted = isset($conflicted[$unitId]);
            if (!$isConflicted && (mt_rand() / mt_getrandmax()) >= $mutationRate) {
                continue;
            }

            $unit = $units[$unitId];
            $this->removeUnitPlacement($chromosome, $unit, $state);

            $placement = $this->selectPositionForUnit(
                $chromosome,
                $unitId,
                $data,
                $context,
                $state,
                stochastic: true
            );

            if ($placement === null) {
                $placement = $this->randomFallbackPosition($unit, $data);
            }

            $this->applyUnitPlacement(
                $chromosome,
                $unit,
                (int) $placement['day'],
                (int) $placement['period'],
                $state
            );

            $mutated++;
        }
    }

    /**
     * Build immutable generation context used by initialization, mutation and repair.
     */
    private function buildGenerationContext(GenerationData $data, Chromosome $template): array {
        [$units, $geneToUnit] = $this->buildGeneUnits($template->genes);

        $breakSet = array_flip($data->breakAfterPeriods);
        $validDoubleStarts = $data->validDoubleStartPeriods;
        if (empty($validDoubleStarts)) {
            $validDoubleStarts = BlockPlacementRules::computeValidDoubleStartPeriods(
                $data->periodsPerDay,
                $data->breakAfterPeriods
            );
        }
        $validDoubleStartSet = array_fill_keys(array_map('intval', $validDoubleStarts), true);
        $lockedTeacher = [];
        $lockedKlass = [];
        $lockedVenue = [];
        $lockedAssistantTeacher = [];
        $lockedGradeCore = [];
        $lockedGradeOptional = [];
        foreach ($data->lockedSlots as $slot) {
            $day = (int) ($slot['day_of_cycle'] ?? 0);
            $teacherId = (int) ($slot['teacher_id'] ?? 0);
            $klassId = (int) ($slot['klass_id'] ?? 0);
            $venueId = (int) ($slot['venue_id'] ?? 0);
            $assistantTeacherId = (int) ($slot['assistant_teacher_id'] ?? 0);
            $gradeId = (int) ($slot['grade_id'] ?? 0);
            $isOptional = (bool) ($slot['is_optional'] ?? (($slot['optional_subject_id'] ?? null) !== null));
            $duration = max(1, (int) ($slot['duration'] ?? 1));
            for ($p = (int) $slot['period_number']; $p < ((int) $slot['period_number']) + $duration; $p++) {
                if ($teacherId > 0) {
                    $lockedTeacher["{$teacherId}:{$day}:{$p}"] = true;
                }
                if ($klassId > 0) {
                    $lockedKlass["{$klassId}:{$day}:{$p}"] = true;
                }
                if ($venueId > 0) {
                    $lockedVenue["{$venueId}:{$day}:{$p}"] = true;
                }
                if ($assistantTeacherId > 0) {
                    $lockedAssistantTeacher["{$assistantTeacherId}:{$day}:{$p}"] = true;
                }
                if ($gradeId > 0) {
                    $gKey = "{$gradeId}:{$day}:{$p}";
                    if ($isOptional) {
                        $lockedGradeOptional[$gKey] = true;
                    } else {
                        $lockedGradeCore[$gKey] = true;
                    }
                }
            }
        }

        $unavailabilitySet = [];
        foreach ($data->teacherUnavailability as $teacherId => $slots) {
            foreach ($slots as $slot) {
                $day = (int) ($slot['day_of_cycle'] ?? 0);
                $period = (int) ($slot['period_number'] ?? 0);
                $unavailabilitySet["{$teacherId}:{$day}:{$period}"] = true;
            }
        }

        $preferenceSets = [];
        foreach ($data->teacherPreferences as $teacherId => $preference) {
            $periods = $preference['preferred_periods'] ?? [];
            if (!empty($periods)) {
                $preferenceSets[(int) $teacherId] = array_flip(array_map('intval', $periods));
            }
        }

        $classTotalPeriods = [];
        foreach ($template->genes as $gene) {
            if ($gene->klassId > 0) {
                $classTotalPeriods[$gene->klassId] = ($classTotalPeriods[$gene->klassId] ?? 0) + $gene->duration;
            }
        }

        $candidateCache = [];
        foreach ($units as $unitId => $unit) {
            $strict = $this->buildCandidatePositionsForUnit(
                $template,
                $unit,
                $data,
                $breakSet,
                $lockedTeacher,
                $lockedKlass,
                $unavailabilitySet,
                true,
                $validDoubleStartSet,
                $lockedVenue,
                $lockedAssistantTeacher,
                $lockedGradeCore,
                $lockedGradeOptional
            );
            $relaxed = $this->buildCandidatePositionsForUnit(
                $template,
                $unit,
                $data,
                $breakSet,
                $lockedTeacher,
                $lockedKlass,
                $unavailabilitySet,
                false,
                $validDoubleStartSet,
                $lockedVenue,
                $lockedAssistantTeacher,
                $lockedGradeCore,
                $lockedGradeOptional
            );

            $candidateCache[$unitId] = [
                'strict' => $strict,
                'relaxed' => $relaxed,
            ];
        }

        // Pre-compute coupling grade labels for same-day constraint
        $couplingGradeLabels = [];
        foreach ($units as $unit) {
            $firstIdx = $unit['indices'][0];
            $gene = $template->genes[$firstIdx];
            if ($gene->couplingKey === null) {
                continue;
            }
            if (preg_match('/^cg_(\d+)_(.+)_[sdt]\d+$/', $gene->couplingKey, $m)) {
                $couplingGradeLabels[(int) $m[1]][$m[2]] = true;
            }
        }

        // Pre-compute locked coupling label days
        $lockedCouplingLabelDay = [];
        foreach ($data->lockedSlots as $slot) {
            $cgKey = $slot['coupling_group_key'] ?? null;
            if ($cgKey === null || trim($cgKey) === '') {
                continue;
            }
            if (preg_match('/^cg_(\d+)_(.+)_[sdt]\d+$/', $cgKey, $m)) {
                $day = (int) ($slot['day_of_cycle'] ?? 0);
                if ($day > 0) {
                    $lockedCouplingLabelDay["{$m[1]}:{$m[2]}:{$day}"] = true;
                }
            }
        }

        // Pre-compute subject-to-pairs lookup for fast scoring
        $subjectToPairs = [];
        foreach ($data->subjectPairs as $pair) {
            $subjectToPairs[(int) $pair['subject_id_a']][] = $pair;
            $subjectToPairs[(int) $pair['subject_id_b']][] = $pair;
        }

        // Pre-compute period restriction allowed sets for O(1) lookup
        $periodRestrictionSets = [];
        foreach ($data->periodRestrictions as $subjectId => $info) {
            $allowedPeriods = $info['allowed_periods'] ?? [];
            if (!empty($allowedPeriods)) {
                $periodRestrictionSets[$subjectId] = array_flip($allowedPeriods);
            }
        }

        return [
            'units' => $units,
            'geneToUnit' => $geneToUnit,
            'templateGenes' => $template->genes,
            'breakSet' => $breakSet,
            'validDoubleStartSet' => $validDoubleStartSet,
            'validDoubleStarts' => array_keys($validDoubleStartSet),
            'lockedTeacher' => $lockedTeacher,
            'lockedKlass' => $lockedKlass,
            'lockedVenue' => $lockedVenue,
            'lockedAssistantTeacher' => $lockedAssistantTeacher,
            'lockedGradeCore' => $lockedGradeCore,
            'lockedGradeOptional' => $lockedGradeOptional,
            'unavailability' => $unavailabilitySet,
            'preferenceSets' => $preferenceSets,
            'candidateCache' => $candidateCache,
            'classTotalPeriods' => $classTotalPeriods,
            'subjectToPairs' => $subjectToPairs,
            'periodRestrictionSets' => $periodRestrictionSets,
            'couplingGradeLabels' => $couplingGradeLabels,
            'lockedCouplingLabelDay' => $lockedCouplingLabelDay,
        ];
    }

    /**
     * Group genes into scheduling units.
     *
     * A regular class-subject gene is a unit of one.
     * A coupling group (same couplingKey) is a single atomic unit.
     *
     * @return array{0: array<int, array{indices: int[], duration: int}>, 1: array<int, int>}
     */
    private function buildGeneUnits(array $genes): array {
        $units = [];
        $geneToUnit = [];
        $couplingKeyToUnit = [];

        foreach ($genes as $idx => $gene) {
            if ($gene->couplingKey === null) {
                $unitId = count($units);
                $units[$unitId] = [
                    'indices' => [$idx],
                    'duration' => $gene->duration,
                ];
                $geneToUnit[$idx] = $unitId;
                continue;
            }

            if (!isset($couplingKeyToUnit[$gene->couplingKey])) {
                $unitId = count($units);
                $couplingKeyToUnit[$gene->couplingKey] = $unitId;
                $units[$unitId] = [
                    'indices' => [],
                    'duration' => $gene->duration,
                ];
            }

            $unitId = $couplingKeyToUnit[$gene->couplingKey];
            $units[$unitId]['indices'][] = $idx;
            $geneToUnit[$idx] = $unitId;
        }

        return [$units, $geneToUnit];
    }

    /**
     * Build all candidate (day,period) placements for a unit.
     */
    private function buildCandidatePositionsForUnit(
        Chromosome $template,
        array $unit,
        GenerationData $data,
        array $breakSet,
        array $lockedTeacher,
        array $lockedKlass,
        array $unavailabilitySet,
        bool $enforceAvailability,
        array $validDoubleStartSet = [],
        array $lockedVenue = [],
        array $lockedAssistantTeacher = [],
        array $lockedGradeCore = [],
        array $lockedGradeOptional = []
    ): array {
        $duration = (int) $unit['duration'];
        $maxPeriod = $data->periodsPerDay - $duration + 1;
        if ($maxPeriod < 1) {
            return [];
        }

        $positions = [];
        for ($day = 1; $day <= $data->cycleDays; $day++) {
            for ($period = 1; $period <= $maxPeriod; $period++) {
                if ($this->durationSpansBreak($period, $duration, $breakSet)) {
                    continue;
                }

                if (BlockPlacementRules::isMisalignedDoubleStart($period, $duration, $validDoubleStartSet)) {
                    continue;
                }

                if ($enforceAvailability) {
                    $blocked = false;
                    foreach ($unit['indices'] as $idx) {
                        /** @var Gene $gene */
                        $gene = $template->genes[$idx];
                        for ($p = $period; $p < $period + $duration; $p++) {
                            if ($gene->teacherId > 0) {
                                if (isset($unavailabilitySet["{$gene->teacherId}:{$day}:{$p}"])) {
                                    $blocked = true;
                                    break 2;
                                }
                                if (isset($lockedTeacher["{$gene->teacherId}:{$day}:{$p}"])) {
                                    $blocked = true;
                                    break 2;
                                }
                            }

                            if ($gene->klassId > 0 && isset($lockedKlass["{$gene->klassId}:{$day}:{$p}"])) {
                                $blocked = true;
                                break 2;
                            }

                            if ($gene->venueId > 0 && isset($lockedVenue["{$gene->venueId}:{$day}:{$p}"])) {
                                $blocked = true;
                                break 2;
                            }

                            if ($gene->assistantTeacherId > 0 && isset($lockedAssistantTeacher["{$gene->assistantTeacherId}:{$day}:{$p}"])) {
                                $blocked = true;
                                break 2;
                            }

                            if ($gene->gradeId > 0) {
                                $gKey = "{$gene->gradeId}:{$day}:{$p}";
                                if ($gene->optionalSubjectId !== null && isset($lockedGradeCore[$gKey])) {
                                    $blocked = true;
                                    break 2;
                                }
                                if ($gene->optionalSubjectId === null && $gene->klassId > 0 && isset($lockedGradeOptional[$gKey])) {
                                    $blocked = true;
                                    break 2;
                                }
                            }
                        }
                    }

                    if ($blocked) {
                        continue;
                    }
                }

                $positions[] = [
                    'day' => $day,
                    'period' => $period,
                ];
            }
        }

        return $positions;
    }

    /**
     * Prioritize units that are hardest to place first.
     */
    private function orderUnitsByDifficulty(array $units, array $candidateCache, GenerationData $data, array $context): array {
        $unitIds = array_keys($units);
        shuffle($unitIds);

        usort($unitIds, function (int $a, int $b) use ($units, $candidateCache, $data, $context): int {
            $aStrict = count($candidateCache[$a]['strict'] ?? []);
            $bStrict = count($candidateCache[$b]['strict'] ?? []);
            $aRelaxed = count($candidateCache[$a]['relaxed'] ?? []);
            $bRelaxed = count($candidateCache[$b]['relaxed'] ?? []);

            $aDomain = $aStrict > 0 ? $aStrict : $aRelaxed;
            $bDomain = $bStrict > 0 ? $bStrict : $bRelaxed;

            if ($aDomain !== $bDomain) {
                return $aDomain <=> $bDomain; // smallest domain first (MRV heuristic)
            }

            $aDuration = (int) ($units[$a]['duration'] ?? 1);
            $bDuration = (int) ($units[$b]['duration'] ?? 1);
            if ($aDuration !== $bDuration) {
                return $bDuration <=> $aDuration; // longer blocks first
            }

            $aPressure = 0;
            $bPressure = 0;
            foreach ($units[$a]['indices'] as $idx) {
                /** @var Gene $geneA */
                $geneA = $context['templateGenes'][$idx] ?? null;
                if ($geneA === null) {
                    continue;
                }
                if ($geneA->teacherId > 0) {
                    $aPressure += count($data->teacherAssignments[$geneA->teacherId] ?? []);
                }
                if ($geneA->klassId > 0) {
                    $aPressure += count($data->klassAssignments[$geneA->klassId] ?? []);
                }
            }
            foreach ($units[$b]['indices'] as $idx) {
                /** @var Gene $geneB */
                $geneB = $context['templateGenes'][$idx] ?? null;
                if ($geneB === null) {
                    continue;
                }
                if ($geneB->teacherId > 0) {
                    $bPressure += count($data->teacherAssignments[$geneB->teacherId] ?? []);
                }
                if ($geneB->klassId > 0) {
                    $bPressure += count($data->klassAssignments[$geneB->klassId] ?? []);
                }
            }

            if ($aPressure !== $bPressure) {
                return $bPressure <=> $aPressure;
            }

            return count($units[$b]['indices']) <=> count($units[$a]['indices']);
        });

        return $unitIds;
    }

    /**
     * Create an empty mutable placement state.
     */
    private function createEmptyPlacementState(): array {
        return [
            'teacher' => [],
            'klass' => [],
            'subjectDay' => [],
            'subjectLessonDay' => [],
            'teacherDayPeriods' => [],
            'klassDay' => [],
            'gradeCore' => [],
            'gradeOptional' => [],
            'venue' => [],
            'assistantTeacher' => [],
            'subjectDayPresence' => [],
            'subjectPeriodDetail' => [],
            'couplingLabelDay' => [],
        ];
    }

    /**
     * Build placement state from an existing chromosome assignment.
     */
    private function buildPlacementStateFromChromosome(Chromosome $chromosome, array $context): array {
        $state = $this->createEmptyPlacementState();

        foreach ($context['units'] as $unit) {
            $firstIdx = $unit['indices'][0];
            $day = $chromosome->genes[$firstIdx]->dayOfCycle;
            $period = $chromosome->genes[$firstIdx]->startPeriod;
            if ($day <= 0 || $period <= 0) {
                continue;
            }

            $duration = (int) $unit['duration'];
            foreach ($unit['indices'] as $idx) {
                $gene = $chromosome->genes[$idx];
                $subjectDayKey = null;
                if ($gene->subjectId > 0 && $gene->klassId > 0) {
                    $subjectDayKey = "{$gene->subjectId}:{$gene->klassId}:{$day}";
                    $state['subjectLessonDay'][$subjectDayKey] = ($state['subjectLessonDay'][$subjectDayKey] ?? 0) + 1;
                }

                for ($p = $period; $p < $period + $duration; $p++) {
                    if ($gene->teacherId > 0) {
                        $tKey = "{$gene->teacherId}:{$day}:{$p}";
                        $state['teacher'][$tKey] = ($state['teacher'][$tKey] ?? 0) + 1;
                        $tdKey = "{$gene->teacherId}:{$day}";
                        $state['teacherDayPeriods'][$tdKey][$p] = ($state['teacherDayPeriods'][$tdKey][$p] ?? 0) + 1;
                    }

                    if ($gene->klassId > 0) {
                        $cKey = "{$gene->klassId}:{$day}:{$p}";
                        $state['klass'][$cKey] = ($state['klass'][$cKey] ?? 0) + 1;
                        $state['klassDay'][$gene->klassId][$day] = ($state['klassDay'][$gene->klassId][$day] ?? 0) + 1;
                    }

                    if ($gene->subjectId > 0 && $gene->klassId > 0) {
                        $sdKey = "{$gene->subjectId}:{$gene->klassId}:{$day}";
                        $state['subjectDay'][$sdKey] = ($state['subjectDay'][$sdKey] ?? 0) + 1;
                        $state['subjectDayPresence'][$sdKey] = true;
                        $state['subjectPeriodDetail'][$sdKey][] = $p;
                    }

                    if ($gene->gradeId > 0) {
                        $gKey = "{$gene->gradeId}:{$day}:{$p}";
                        if ($gene->optionalSubjectId !== null) {
                            $state['gradeOptional'][$gKey] = ($state['gradeOptional'][$gKey] ?? 0) + 1;
                        } elseif ($gene->klassId > 0) {
                            $state['gradeCore'][$gKey] = ($state['gradeCore'][$gKey] ?? 0) + 1;
                        }
                    }

                    if ($gene->venueId > 0) {
                        $vKey = "{$gene->venueId}:{$day}:{$p}";
                        $state['venue'][$vKey] = ($state['venue'][$vKey] ?? 0) + 1;
                    }

                    if ($gene->assistantTeacherId > 0) {
                        $aKey = "{$gene->assistantTeacherId}:{$day}:{$p}";
                        $state['assistantTeacher'][$aKey] = ($state['assistantTeacher'][$aKey] ?? 0) + 1;
                    }
                }
            }

            // Track coupling label day (per unit, not per gene)
            $firstGene = $chromosome->genes[$firstIdx];
            if ($firstGene->couplingKey !== null) {
                if (preg_match('/^cg_(\d+)_(.+)_[sdt]\d+$/', $firstGene->couplingKey, $m)) {
                    $cldKey = "{$m[1]}:{$m[2]}:{$day}";
                    $state['couplingLabelDay'][$cldKey] = ($state['couplingLabelDay'][$cldKey] ?? 0) + 1;
                }
            }
        }

        return $state;
    }

    /**
     * Apply a placement and update state counts.
     */
    private function applyUnitPlacement(
        Chromosome $chromosome,
        array $unit,
        int $day,
        int $period,
        array &$state
    ): void {
        $duration = (int) $unit['duration'];

        foreach ($unit['indices'] as $idx) {
            $gene = $chromosome->genes[$idx];
            $gene->dayOfCycle = $day;
            $gene->startPeriod = $period;
            $subjectDayKey = null;
            if ($gene->subjectId > 0 && $gene->klassId > 0) {
                $subjectDayKey = "{$gene->subjectId}:{$gene->klassId}:{$day}";
                $state['subjectLessonDay'][$subjectDayKey] = ($state['subjectLessonDay'][$subjectDayKey] ?? 0) + 1;
            }

            for ($p = $period; $p < $period + $duration; $p++) {
                if ($gene->teacherId > 0) {
                    $tKey = "{$gene->teacherId}:{$day}:{$p}";
                    $state['teacher'][$tKey] = ($state['teacher'][$tKey] ?? 0) + 1;
                    $tdKey = "{$gene->teacherId}:{$day}";
                    $state['teacherDayPeriods'][$tdKey][$p] = ($state['teacherDayPeriods'][$tdKey][$p] ?? 0) + 1;
                }

                if ($gene->klassId > 0) {
                    $cKey = "{$gene->klassId}:{$day}:{$p}";
                    $state['klass'][$cKey] = ($state['klass'][$cKey] ?? 0) + 1;
                    $state['klassDay'][$gene->klassId][$day] = ($state['klassDay'][$gene->klassId][$day] ?? 0) + 1;
                }

                if ($gene->subjectId > 0 && $gene->klassId > 0) {
                    $sdKey = "{$gene->subjectId}:{$gene->klassId}:{$day}";
                    $state['subjectDay'][$sdKey] = ($state['subjectDay'][$sdKey] ?? 0) + 1;
                    $state['subjectDayPresence'][$sdKey] = true;
                    $state['subjectPeriodDetail'][$sdKey][] = $p;
                }

                if ($gene->gradeId > 0) {
                    $gKey = "{$gene->gradeId}:{$day}:{$p}";
                    if ($gene->optionalSubjectId !== null) {
                        $state['gradeOptional'][$gKey] = ($state['gradeOptional'][$gKey] ?? 0) + 1;
                    } elseif ($gene->klassId > 0) {
                        $state['gradeCore'][$gKey] = ($state['gradeCore'][$gKey] ?? 0) + 1;
                    }
                }

                if ($gene->venueId > 0) {
                    $vKey = "{$gene->venueId}:{$day}:{$p}";
                    $state['venue'][$vKey] = ($state['venue'][$vKey] ?? 0) + 1;
                }

                if ($gene->assistantTeacherId > 0) {
                    $aKey = "{$gene->assistantTeacherId}:{$day}:{$p}";
                    $state['assistantTeacher'][$aKey] = ($state['assistantTeacher'][$aKey] ?? 0) + 1;
                }
            }
        }

        // Track coupling label day (per unit)
        $firstGene = $chromosome->genes[$unit['indices'][0]];
        if ($firstGene->couplingKey !== null) {
            if (preg_match('/^cg_(\d+)_(.+)_[sdt]\d+$/', $firstGene->couplingKey, $m)) {
                $cldKey = "{$m[1]}:{$m[2]}:{$day}";
                $state['couplingLabelDay'][$cldKey] = ($state['couplingLabelDay'][$cldKey] ?? 0) + 1;
            }
        }
    }

    /**
     * Remove a placement and update state counts.
     */
    private function removeUnitPlacement(Chromosome $chromosome, array $unit, array &$state): void {
        $firstIdx = $unit['indices'][0];
        $day = $chromosome->genes[$firstIdx]->dayOfCycle;
        $period = $chromosome->genes[$firstIdx]->startPeriod;
        $duration = (int) $unit['duration'];

        if ($day <= 0 || $period <= 0) {
            return;
        }

        // Decrement coupling label day (per unit, before zeroing genes)
        $firstGene = $chromosome->genes[$unit['indices'][0]];
        if ($firstGene->couplingKey !== null) {
            if (preg_match('/^cg_(\d+)_(.+)_[sdt]\d+$/', $firstGene->couplingKey, $m)) {
                $cldKey = "{$m[1]}:{$m[2]}:{$day}";
                if (isset($state['couplingLabelDay'][$cldKey])) {
                    $state['couplingLabelDay'][$cldKey]--;
                    if ($state['couplingLabelDay'][$cldKey] <= 0) {
                        unset($state['couplingLabelDay'][$cldKey]);
                    }
                }
            }
        }

        foreach ($unit['indices'] as $idx) {
            $gene = $chromosome->genes[$idx];
            $subjectDayKey = null;
            if ($gene->subjectId > 0 && $gene->klassId > 0) {
                $subjectDayKey = "{$gene->subjectId}:{$gene->klassId}:{$day}";
                if (isset($state['subjectLessonDay'][$subjectDayKey])) {
                    $state['subjectLessonDay'][$subjectDayKey]--;
                    if ($state['subjectLessonDay'][$subjectDayKey] <= 0) {
                        unset($state['subjectLessonDay'][$subjectDayKey]);
                    }
                }
            }
            for ($p = $period; $p < $period + $duration; $p++) {
                if ($gene->teacherId > 0) {
                    $tKey = "{$gene->teacherId}:{$day}:{$p}";
                    if (isset($state['teacher'][$tKey])) {
                        $state['teacher'][$tKey]--;
                        if ($state['teacher'][$tKey] <= 0) {
                            unset($state['teacher'][$tKey]);
                        }
                    }

                    $tdKey = "{$gene->teacherId}:{$day}";
                    if (isset($state['teacherDayPeriods'][$tdKey][$p])) {
                        $state['teacherDayPeriods'][$tdKey][$p]--;
                        if ($state['teacherDayPeriods'][$tdKey][$p] <= 0) {
                            unset($state['teacherDayPeriods'][$tdKey][$p]);
                        }
                        if (empty($state['teacherDayPeriods'][$tdKey])) {
                            unset($state['teacherDayPeriods'][$tdKey]);
                        }
                    }
                }

                if ($gene->klassId > 0) {
                    $cKey = "{$gene->klassId}:{$day}:{$p}";
                    if (isset($state['klass'][$cKey])) {
                        $state['klass'][$cKey]--;
                        if ($state['klass'][$cKey] <= 0) {
                            unset($state['klass'][$cKey]);
                        }
                    }

                    if (isset($state['klassDay'][$gene->klassId][$day])) {
                        $state['klassDay'][$gene->klassId][$day]--;
                        if ($state['klassDay'][$gene->klassId][$day] <= 0) {
                            unset($state['klassDay'][$gene->klassId][$day]);
                        }
                        if (empty($state['klassDay'][$gene->klassId])) {
                            unset($state['klassDay'][$gene->klassId]);
                        }
                    }
                }

                if ($gene->subjectId > 0 && $gene->klassId > 0) {
                    $sdKey = "{$gene->subjectId}:{$gene->klassId}:{$day}";
                    if (isset($state['subjectDay'][$sdKey])) {
                        $state['subjectDay'][$sdKey]--;
                        if ($state['subjectDay'][$sdKey] <= 0) {
                            unset($state['subjectDay'][$sdKey]);
                            unset($state['subjectDayPresence'][$sdKey]);
                            unset($state['subjectPeriodDetail'][$sdKey]);
                        } elseif (isset($state['subjectPeriodDetail'][$sdKey])) {
                            // Remove this specific period from the detail list
                            $periodIdx = array_search($p, $state['subjectPeriodDetail'][$sdKey], true);
                            if ($periodIdx !== false) {
                                array_splice($state['subjectPeriodDetail'][$sdKey], $periodIdx, 1);
                            }
                        }
                    }
                }

                if ($gene->gradeId > 0) {
                    $gKey = "{$gene->gradeId}:{$day}:{$p}";
                    if ($gene->optionalSubjectId !== null) {
                        if (isset($state['gradeOptional'][$gKey])) {
                            $state['gradeOptional'][$gKey]--;
                            if ($state['gradeOptional'][$gKey] <= 0) {
                                unset($state['gradeOptional'][$gKey]);
                            }
                        }
                    } elseif ($gene->klassId > 0) {
                        if (isset($state['gradeCore'][$gKey])) {
                            $state['gradeCore'][$gKey]--;
                            if ($state['gradeCore'][$gKey] <= 0) {
                                unset($state['gradeCore'][$gKey]);
                            }
                        }
                    }
                }

                if ($gene->venueId > 0) {
                    $vKey = "{$gene->venueId}:{$day}:{$p}";
                    if (isset($state['venue'][$vKey])) {
                        $state['venue'][$vKey]--;
                        if ($state['venue'][$vKey] <= 0) {
                            unset($state['venue'][$vKey]);
                        }
                    }
                }

                if ($gene->assistantTeacherId > 0) {
                    $aKey = "{$gene->assistantTeacherId}:{$day}:{$p}";
                    if (isset($state['assistantTeacher'][$aKey])) {
                        $state['assistantTeacher'][$aKey]--;
                        if ($state['assistantTeacher'][$aKey] <= 0) {
                            unset($state['assistantTeacher'][$aKey]);
                        }
                    }
                }
            }

            $gene->dayOfCycle = 0;
            $gene->startPeriod = 0;
        }
    }

    /**
     * Choose the best placement candidate for a unit.
     */
    private function selectPositionForUnit(
        Chromosome $chromosome,
        int $unitId,
        GenerationData $data,
        array $context,
        array $state,
        bool $stochastic
    ): ?array {
        $candidateSet = $context['candidateCache'][$unitId]['strict'] ?? [];
        if (empty($candidateSet)) {
            $candidateSet = $context['candidateCache'][$unitId]['relaxed'] ?? [];
        }
        if (empty($candidateSet)) {
            return null;
        }

        $scores = [];
        foreach ($candidateSet as $candidate) {
            $score = $this->scorePlacementForUnit(
                $chromosome,
                $context['units'][$unitId],
                (int) $candidate['day'],
                (int) $candidate['period'],
                $data,
                $context,
                $state
            );

            $scores[] = [
                'day' => (int) $candidate['day'],
                'period' => (int) $candidate['period'],
                'score' => $score,
            ];
        }

        usort($scores, fn(array $a, array $b) => $a['score'] <=> $b['score']);
        if (empty($scores)) {
            return null;
        }

        if (!$stochastic || count($scores) === 1) {
            return ['day' => $scores[0]['day'], 'period' => $scores[0]['period']];
        }

        $poolSize = min($this->heuristicTopK, count($scores));
        $r = mt_rand() / mt_getrandmax();
        $index = (int) floor(($r ** 2) * $poolSize);
        if ($index >= $poolSize) {
            $index = $poolSize - 1;
        }

        return ['day' => $scores[$index]['day'], 'period' => $scores[$index]['period']];
    }

    /**
     * Score a candidate placement for one unit.
     *
     * Hard penalties dominate to drive feasibility; soft penalties guide quality.
     */
    private function scorePlacementForUnit(
        Chromosome $chromosome,
        array $unit,
        int $day,
        int $period,
        GenerationData $data,
        array $context,
        array $state
    ): float {
        $duration = (int) $unit['duration'];
        $hard = 0.0;
        $soft = 0.0;
        $teacherAddedPeriods = [];

        foreach ($unit['indices'] as $idx) {
            $gene = $chromosome->genes[$idx];

            for ($p = $period; $p < $period + $duration; $p++) {
                if ($p < 1 || $p > $data->periodsPerDay) {
                    $hard += 1500;
                    continue;
                }

                if ($gene->gradeId > 0) {
                    $gKey = "{$gene->gradeId}:{$day}:{$p}";
                    if ($gene->optionalSubjectId !== null) {
                        $coreCount = (int) ($state['gradeCore'][$gKey] ?? 0);
                        if ($coreCount > 0) {
                            $hard += 1500 * (float) $coreCount;
                        }
                        if (isset($context['lockedGradeCore'][$gKey])) {
                            $hard += 1500;
                        }
                    } elseif ($gene->klassId > 0) {
                        $optionalCount = (int) ($state['gradeOptional'][$gKey] ?? 0);
                        if ($optionalCount > 0) {
                            $hard += 1500 * (float) $optionalCount;
                        }
                        if (isset($context['lockedGradeOptional'][$gKey])) {
                            $hard += 1500;
                        }
                    }
                }

                if ($gene->teacherId > 0) {
                    $tKey = "{$gene->teacherId}:{$day}:{$p}";
                    if (($state['teacher'][$tKey] ?? 0) > 0) {
                        $hard += 1000 * (float) $state['teacher'][$tKey];
                    }
                    if (isset($context['lockedTeacher'][$tKey])) {
                        $hard += 1500;
                    }
                    if (isset($context['unavailability'][$tKey])) {
                        $hard += 1500;
                    }
                    if (isset($context['preferenceSets'][$gene->teacherId]) && !isset($context['preferenceSets'][$gene->teacherId][$p])) {
                        $soft += 5.0;
                    }

                    $teacherAddedPeriods[$gene->teacherId][$day][$p] = true;
                }

                if ($gene->klassId > 0) {
                    $cKey = "{$gene->klassId}:{$day}:{$p}";
                    if (($state['klass'][$cKey] ?? 0) > 0) {
                        $hard += 1000 * (float) $state['klass'][$cKey];
                    }
                    if (isset($context['lockedKlass'][$cKey])) {
                        $hard += 1500;
                    }
                }

                if ($gene->venueId > 0) {
                    $vKey = "{$gene->venueId}:{$day}:{$p}";
                    if (($state['venue'][$vKey] ?? 0) > 0) {
                        $hard += 1000 * (float) $state['venue'][$vKey];
                    }
                    if (isset($context['lockedVenue'][$vKey])) {
                        $hard += 1500;
                    }
                }

                if ($gene->assistantTeacherId > 0) {
                    $aKey = "{$gene->assistantTeacherId}:{$day}:{$p}";
                    if (($state['assistantTeacher'][$aKey] ?? 0) > 0) {
                        $hard += 1000 * (float) $state['assistantTeacher'][$aKey];
                    }
                    if (isset($context['lockedAssistantTeacher'][$aKey])) {
                        $hard += 1500;
                    }
                }

                // Period restriction penalty
                if ($gene->subjectId > 0 && isset($context['periodRestrictionSets'][$gene->subjectId]) && !isset($context['periodRestrictionSets'][$gene->subjectId][$p])) {
                    $restriction = $data->periodRestrictions[$gene->subjectId]['restriction'] ?? '';
                    $soft += $restriction === 'fixed_period' ? 5.0 : ($restriction === 'afternoon_only' ? 3.0 : 4.0);
                }
            }

            if ($gene->subjectId > 0 && $gene->klassId > 0 && isset($data->subjectSpreads[$gene->subjectId])) {
                $sdKey = "{$gene->subjectId}:{$gene->klassId}:{$day}";
                $projectedLessons = ($state['subjectLessonDay'][$sdKey] ?? 0) + 1;
                $max = (int) ($data->subjectSpreads[$gene->subjectId]['max_lessons_per_day'] ?? 0);
                if ($max > 0 && $projectedLessons > $max) {
                    $soft += 6.0 * ($projectedLessons - $max);
                }
            }

            if ($gene->klassId > 0) {
                $currentLoad = (int) ($state['klassDay'][$gene->klassId][$day] ?? 0);
                $target = (int) ceil((($context['classTotalPeriods'][$gene->klassId] ?? 0) / max(1, $data->cycleDays)));
                if ($target > 0 && ($currentLoad + $duration) > ($target + 1)) {
                    $soft += (float) (($currentLoad + $duration) - ($target + 1));
                }
            }
        }

        // Intra-unit venue conflicts: multiple genes in the same coupling group sharing a venue
        if (count($unit['indices']) > 1) {
            $unitVenueCounts = [];
            foreach ($unit['indices'] as $idx) {
                $gene = $chromosome->genes[$idx];
                if ($gene->venueId > 0) {
                    $unitVenueCounts[$gene->venueId] = ($unitVenueCounts[$gene->venueId] ?? 0) + 1;
                }
            }
            foreach ($unitVenueCounts as $count) {
                if ($count > 1) {
                    $hard += 1000 * ($count - 1) * $duration;
                }
            }
        }

        if ($this->durationSpansBreak($period, $duration, $context['breakSet'])) {
            $hard += 1500;
        }
        if (BlockPlacementRules::isMisalignedDoubleStart($period, $duration, $context['validDoubleStartSet'] ?? [])) {
            $hard += 1500;
        }

        // Coupling day conflict: different labels of same grade must not share a day
        $firstGene = $chromosome->genes[$unit['indices'][0]];
        if ($firstGene->couplingKey !== null) {
            if (preg_match('/^cg_(\d+)_(.+)_[sdt]\d+$/', $firstGene->couplingKey, $m)) {
                $cgGradeId = (int) $m[1];
                $cgLabel = $m[2];
                foreach (($context['couplingGradeLabels'][$cgGradeId] ?? []) as $otherLabel => $_) {
                    if ($otherLabel === $cgLabel) {
                        continue;
                    }
                    $cldKey = "{$cgGradeId}:{$otherLabel}:{$day}";
                    if (($state['couplingLabelDay'][$cldKey] ?? 0) > 0 || isset($context['lockedCouplingLabelDay'][$cldKey])) {
                        $hard += 1500;
                    }
                }
            }
        }

        // Subject pair penalty
        if (!empty($context['subjectToPairs'])) {
            foreach ($unit['indices'] as $idx) {
                $gene = $chromosome->genes[$idx];
                if ($gene->subjectId <= 0 || $gene->klassId <= 0) {
                    continue;
                }
                $pairs = $context['subjectToPairs'][$gene->subjectId] ?? [];
                foreach ($pairs as $pair) {
                    $otherSubjectId = ($pair['subject_id_a'] === $gene->subjectId) ? $pair['subject_id_b'] : $pair['subject_id_a'];
                    $pairKlassId = $pair['klass_id'] ?? null;
                    if ($pairKlassId !== null && $pairKlassId !== $gene->klassId) {
                        continue;
                    }
                    $otherKey = "{$otherSubjectId}:{$gene->klassId}:{$day}";
                    $otherPresent = isset($state['subjectDayPresence'][$otherKey]);

                    switch ($pair['rule']) {
                        case 'not_same_day':
                            if ($otherPresent) {
                                $soft += 4.0;
                            }
                            break;
                        case 'must_same_day':
                            // Can't fully evaluate during scoring; skip
                            break;
                        case 'not_consecutive':
                            if ($otherPresent) {
                                $otherPeriods = $state['subjectPeriodDetail'][$otherKey] ?? [];
                                for ($p = $period; $p < $period + $duration; $p++) {
                                    foreach ($otherPeriods as $oP) {
                                        if (abs($p - $oP) === 1) {
                                            $soft += 3.0;
                                        }
                                    }
                                }
                            }
                            break;
                        case 'must_follow':
                            if ($otherPresent) {
                                $otherPeriods = $state['subjectPeriodDetail'][$otherKey] ?? [];
                                $adjacent = false;
                                for ($p = $period; $p < $period + $duration; $p++) {
                                    foreach ($otherPeriods as $oP) {
                                        if (abs($p - $oP) === 1) {
                                            $adjacent = true;
                                            break 2;
                                        }
                                    }
                                }
                                if (!$adjacent) {
                                    $soft += 4.0;
                                }
                            }
                            break;
                    }
                }
            }
        }

        foreach ($teacherAddedPeriods as $teacherId => $days) {
            $maxConsecutive = $this->getConsecutiveLimit($teacherId, $data->consecutiveLimits);
            if ($maxConsecutive === null) {
                continue;
            }

            foreach ($days as $dayKey => $addedSet) {
                $tdKey = "{$teacherId}:{$dayKey}";
                $existingPeriods = $state['teacherDayPeriods'][$tdKey] ?? [];
                $longest = $this->longestRunWithAdditions($existingPeriods, array_keys($addedSet));
                if ($longest > $maxConsecutive) {
                    $soft += 4.0 * ($longest - $maxConsecutive);
                }
            }
        }

        return $hard + $soft;
    }

    /**
     * Identify conflicted units from current state (hard constraints only).
     *
     * @return array<int, int> unit_id => conflict count
     */
    private function collectConflictedUnits(Chromosome $chromosome, GenerationData $data, array $context, array $state): array {
        $conflicted = [];

        foreach ($context['units'] as $unitId => $unit) {
            $firstIdx = $unit['indices'][0];
            $day = $chromosome->genes[$firstIdx]->dayOfCycle;
            $period = $chromosome->genes[$firstIdx]->startPeriod;
            $duration = (int) $unit['duration'];

            if ($day <= 0 || $period <= 0) {
                continue;
            }

            if ($this->durationSpansBreak($period, $duration, $context['breakSet'])) {
                $conflicted[$unitId] = ($conflicted[$unitId] ?? 0) + 1;
            }
            if (BlockPlacementRules::isMisalignedDoubleStart($period, $duration, $context['validDoubleStartSet'] ?? [])) {
                $conflicted[$unitId] = ($conflicted[$unitId] ?? 0) + 1;
            }

            foreach ($unit['indices'] as $idx) {
                $gene = $chromosome->genes[$idx];

                for ($p = $period; $p < $period + $duration; $p++) {
                    if ($p < 1 || $p > $data->periodsPerDay) {
                        $conflicted[$unitId] = ($conflicted[$unitId] ?? 0) + 1;
                        continue;
                    }

                    if ($gene->gradeId > 0) {
                        $gKey = "{$gene->gradeId}:{$day}:{$p}";
                        if ($gene->optionalSubjectId !== null) {
                            if (($state['gradeCore'][$gKey] ?? 0) > 0) {
                                $conflicted[$unitId] = ($conflicted[$unitId] ?? 0) + 1;
                            }
                            if (isset($context['lockedGradeCore'][$gKey])) {
                                $conflicted[$unitId] = ($conflicted[$unitId] ?? 0) + 1;
                            }
                        } elseif ($gene->klassId > 0) {
                            if (($state['gradeOptional'][$gKey] ?? 0) > 0) {
                                $conflicted[$unitId] = ($conflicted[$unitId] ?? 0) + 1;
                            }
                            if (isset($context['lockedGradeOptional'][$gKey])) {
                                $conflicted[$unitId] = ($conflicted[$unitId] ?? 0) + 1;
                            }
                        }
                    }

                    if ($gene->teacherId > 0) {
                        $tKey = "{$gene->teacherId}:{$day}:{$p}";
                        if (($state['teacher'][$tKey] ?? 0) > 1) {
                            $conflicted[$unitId] = ($conflicted[$unitId] ?? 0) + 1;
                        }
                        if (isset($context['lockedTeacher'][$tKey]) || isset($context['unavailability'][$tKey])) {
                            $conflicted[$unitId] = ($conflicted[$unitId] ?? 0) + 1;
                        }
                    }

                    if ($gene->klassId > 0) {
                        $cKey = "{$gene->klassId}:{$day}:{$p}";
                        if (($state['klass'][$cKey] ?? 0) > 1) {
                            $conflicted[$unitId] = ($conflicted[$unitId] ?? 0) + 1;
                        }
                        if (isset($context['lockedKlass'][$cKey])) {
                            $conflicted[$unitId] = ($conflicted[$unitId] ?? 0) + 1;
                        }
                    }

                    if ($gene->venueId > 0) {
                        $vKey = "{$gene->venueId}:{$day}:{$p}";
                        if (($state['venue'][$vKey] ?? 0) > 1) {
                            $conflicted[$unitId] = ($conflicted[$unitId] ?? 0) + 1;
                        }
                        if (isset($context['lockedVenue'][$vKey])) {
                            $conflicted[$unitId] = ($conflicted[$unitId] ?? 0) + 1;
                        }
                    }

                    if ($gene->assistantTeacherId > 0) {
                        $aKey = "{$gene->assistantTeacherId}:{$day}:{$p}";
                        if (($state['assistantTeacher'][$aKey] ?? 0) > 1) {
                            $conflicted[$unitId] = ($conflicted[$unitId] ?? 0) + 1;
                        }
                        if (isset($context['lockedAssistantTeacher'][$aKey])) {
                            $conflicted[$unitId] = ($conflicted[$unitId] ?? 0) + 1;
                        }
                    }
                }
            }

            // Coupling day conflict
            $firstGene = $chromosome->genes[$firstIdx];
            if ($firstGene->couplingKey !== null) {
                if (preg_match('/^cg_(\d+)_(.+)_[sdt]\d+$/', $firstGene->couplingKey, $m)) {
                    $cgGradeId = (int) $m[1];
                    $cgLabel = $m[2];
                    foreach (($context['couplingGradeLabels'][$cgGradeId] ?? []) as $otherLabel => $_) {
                        if ($otherLabel === $cgLabel) {
                            continue;
                        }
                        $cldKey = "{$cgGradeId}:{$otherLabel}:{$day}";
                        if (($state['couplingLabelDay'][$cldKey] ?? 0) > 0 || isset($context['lockedCouplingLabelDay'][$cldKey])) {
                            $conflicted[$unitId] = ($conflicted[$unitId] ?? 0) + 1;
                        }
                    }
                }
            }
        }

        return $conflicted;
    }

    /**
     * Local min-conflicts repair for hard-constraint violations.
     */
    private function repairChromosome(Chromosome $chromosome, GenerationData $data, array $context, int $maxMoves): void {
        if ($maxMoves <= 0) {
            return;
        }

        $state = $this->buildPlacementStateFromChromosome($chromosome, $context);

        for ($move = 0; $move < $maxMoves; $move++) {
            $conflicted = $this->collectConflictedUnits($chromosome, $data, $context, $state);
            if (empty($conflicted)) {
                break;
            }

            $candidateIds = array_keys($conflicted);
            usort($candidateIds, fn(int $a, int $b) => ($conflicted[$b] ?? 0) <=> ($conflicted[$a] ?? 0));
            $unitId = $candidateIds[0];
            $unit = $context['units'][$unitId];

            $this->removeUnitPlacement($chromosome, $unit, $state);

            $placement = $this->selectPositionForUnit(
                $chromosome,
                $unitId,
                $data,
                $context,
                $state,
                stochastic: false
            );

            if ($placement === null) {
                $placement = $this->randomFallbackPosition($unit, $data);
            }

            $this->applyUnitPlacement(
                $chromosome,
                $unit,
                (int) $placement['day'],
                (int) $placement['period'],
                $state
            );
        }
    }

    /**
     * Resolve consecutive limit for teacher (teacher-specific overrides global).
     */
    private function getConsecutiveLimit(int $teacherId, array $consecutiveLimits): ?int {
        if (isset($consecutiveLimits[$teacherId])) {
            return (int) ($consecutiveLimits[$teacherId]['max_consecutive_periods'] ?? 0);
        }

        if (isset($consecutiveLimits['global'])) {
            return (int) ($consecutiveLimits['global']['max_consecutive_periods'] ?? 0);
        }

        return null;
    }

    /**
     * Compute longest consecutive run after adding candidate periods.
     */
    private function longestRunWithAdditions(array $existingCounts, array $addedPeriods): int {
        $periods = [];
        foreach ($existingCounts as $period => $count) {
            if ($count > 0) {
                $periods[(int) $period] = true;
            }
        }
        foreach ($addedPeriods as $period) {
            $periods[(int) $period] = true;
        }

        if (empty($periods)) {
            return 0;
        }

        $sorted = array_keys($periods);
        sort($sorted);

        $maxRun = 1;
        $current = 1;
        for ($i = 1, $count = count($sorted); $i < $count; $i++) {
            if ($sorted[$i] === $sorted[$i - 1] + 1) {
                $current++;
                $maxRun = max($maxRun, $current);
            } else {
                $current = 1;
            }
        }

        return $maxRun;
    }

    /**
     * Check whether a duration block crosses a configured break.
     */
    private function durationSpansBreak(int $period, int $duration, array $breakSet): bool {
        if ($duration <= 1) {
            return false;
        }

        for ($p = $period; $p < $period + $duration - 1; $p++) {
            if (isset($breakSet[$p])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fallback random position used when domains are empty (infeasible edge cases).
     */
    private function randomFallbackPosition(array $unit, GenerationData $data): array {
        $duration = (int) ($unit['duration'] ?? 1);
        $maxPeriod = max(1, $data->periodsPerDay - $duration + 1);
        $period = 1;

        if ($duration === 2) {
            $validStarts = $data->validDoubleStartPeriods;
            if (empty($validStarts)) {
                $validStarts = BlockPlacementRules::computeValidDoubleStartPeriods(
                    $data->periodsPerDay,
                    $data->breakAfterPeriods
                );
            }
            if (!empty($validStarts)) {
                $period = (int) $validStarts[array_rand($validStarts)];
            } else {
                $period = mt_rand(1, $maxPeriod);
            }
        } else {
            $period = mt_rand(1, $maxPeriod);
        }

        return [
            'day' => mt_rand(1, max(1, $data->cycleDays)),
            'period' => $period,
        ];
    }

    /**
     * Post-GA venue conflict resolution.
     *
     * Scans the best chromosome for venue double-bookings and attempts to reassign
     * one of the conflicting genes to an alternative venue of the correct type.
     * The GA core is not modified — this is a polishing step.
     */
    private function resolveVenueConflicts(Chromosome $chromosome, GenerationData $data, array $context): void {
        if (empty($data->venuesByType)) {
            return;
        }

        // Build occupancy map: "venueId:day:period" => [gene index, ...]
        $venueSlotGenes = [];
        foreach ($chromosome->genes as $idx => $gene) {
            if ($gene->venueId <= 0 || $gene->dayOfCycle <= 0) {
                continue;
            }
            for ($p = $gene->startPeriod; $p < $gene->startPeriod + $gene->duration; $p++) {
                $key = "{$gene->venueId}:{$gene->dayOfCycle}:{$p}";
                $venueSlotGenes[$key][] = $idx;
            }
        }

        // Track which genes have already been reassigned to prevent cascading swaps
        $reassigned = [];

        // Process each conflict (entries with 2+ genes)
        foreach ($venueSlotGenes as $key => $geneIndices) {
            if (count($geneIndices) < 2) {
                continue;
            }

            [$venueId, $day, $period] = array_map('intval', explode(':', $key));

            // Check if a locked slot also holds this venue here
            $lockedHere = isset($context['lockedVenue']["{$venueId}:{$day}:{$period}"]);

            // Determine which genes to try reassigning
            if ($lockedHere) {
                // Locked slot owns the venue — all chromosome genes at this slot must move
                $genesToReassign = $geneIndices;
            } else {
                // Check if any gene is home-room protected
                $homeRoomIdx = null;
                foreach ($geneIndices as $gIdx) {
                    $g = $chromosome->genes[$gIdx];
                    if ($g->teacherId > 0
                        && isset($data->teacherRoomAssignments[$g->teacherId])
                        && $data->teacherRoomAssignments[$g->teacherId] === $g->venueId
                    ) {
                        $homeRoomIdx = $gIdx;
                        break;
                    }
                }

                if ($homeRoomIdx !== null) {
                    // Home-room gene stays; others move
                    $genesToReassign = array_values(
                        array_filter($geneIndices, fn(int $i) => $i !== $homeRoomIdx)
                    );
                } else {
                    // Keep the first gene, reassign the rest
                    $genesToReassign = array_slice($geneIndices, 1);
                }
            }

            foreach ($genesToReassign as $geneIdx) {
                if (isset($reassigned[$geneIdx])) {
                    continue;
                }

                $gene = $chromosome->genes[$geneIdx];
                $pool = $this->getVenuePoolForGene($gene, $data);

                // Exclude the current venue from pool
                $pool = array_values(array_filter($pool, fn(int $v) => $v !== $gene->venueId));
                if (empty($pool)) {
                    continue;
                }

                $newVenue = $this->findAvailableVenue(
                    $pool,
                    $gene->dayOfCycle,
                    $gene->startPeriod,
                    $gene->duration,
                    $venueSlotGenes,
                    $context['lockedVenue']
                );

                if ($newVenue === null) {
                    continue;
                }

                // Remove old occupancy entries
                for ($p = $gene->startPeriod; $p < $gene->startPeriod + $gene->duration; $p++) {
                    $oldKey = "{$gene->venueId}:{$gene->dayOfCycle}:{$p}";
                    if (isset($venueSlotGenes[$oldKey])) {
                        $venueSlotGenes[$oldKey] = array_values(
                            array_filter($venueSlotGenes[$oldKey], fn(int $i) => $i !== $geneIdx)
                        );
                        if (empty($venueSlotGenes[$oldKey])) {
                            unset($venueSlotGenes[$oldKey]);
                        }
                    }
                }

                // Apply new venue
                $gene->venueId = $newVenue;

                // Add new occupancy entries
                for ($p = $gene->startPeriod; $p < $gene->startPeriod + $gene->duration; $p++) {
                    $newKey = "{$newVenue}:{$gene->dayOfCycle}:{$p}";
                    $venueSlotGenes[$newKey][] = $geneIdx;
                }

                $reassigned[$geneIdx] = true;
            }
        }
    }

    /**
     * Get the pool of candidate venues for a gene based on room requirements.
     *
     * @return int[] Venue IDs that are valid for this gene's subject
     */
    private function getVenuePoolForGene(Gene $gene, GenerationData $data): array {
        if (isset($data->roomRequirements[$gene->subjectId])) {
            $requiredType = strtolower(trim($data->roomRequirements[$gene->subjectId]));
            return $data->venuesByType[$requiredType] ?? [];
        }

        // Default: classrooms, fallback to all venues
        if (!empty($data->venuesByType['classroom'])) {
            return $data->venuesByType['classroom'];
        }

        // Flatten all venue IDs as last resort
        $all = [];
        foreach ($data->venuesByType as $venues) {
            foreach ($venues as $v) {
                $all[] = $v;
            }
        }
        return $all;
    }

    /**
     * Find an available venue from a pool for a given time slot.
     *
     * @param int[] $pool Candidate venue IDs
     * @param int $day Day of cycle
     * @param int $startPeriod Starting period
     * @param int $duration Block duration
     * @param array $venueSlotGenes Current occupancy map
     * @param array $lockedVenue Locked venue occupancy from context
     * @return int|null Available venue ID, or null if none found
     */
    private function findAvailableVenue(
        array $pool,
        int $day,
        int $startPeriod,
        int $duration,
        array $venueSlotGenes,
        array $lockedVenue
    ): ?int {
        foreach ($pool as $venueId) {
            $available = true;
            for ($p = $startPeriod; $p < $startPeriod + $duration; $p++) {
                $key = "{$venueId}:{$day}:{$p}";
                if (!empty($venueSlotGenes[$key]) || isset($lockedVenue[$key])) {
                    $available = false;
                    break;
                }
            }
            if ($available) {
                return $venueId;
            }
        }
        return null;
    }

    /**
     * Pre-flight checks before GA starts.
     *
     * @return string[] Error messages (empty = OK to proceed)
     */
    public function validatePreConditions(GenerationData $data): array {
        $errors = [];

        $validDoubleStarts = $data->validDoubleStartPeriods;
        if (empty($validDoubleStarts)) {
            $validDoubleStarts = BlockPlacementRules::computeValidDoubleStartPeriods(
                $data->periodsPerDay,
                $data->breakAfterPeriods
            );
        }

        if (empty($data->klassSubjects) && empty($data->couplingGroups)) {
            $errors[] = 'No block allocations found. Go to Period Settings and configure how many singles, doubles, and triples each subject needs per cycle.';
        }

        if ($data->periodsPerDay <= 0) {
            $errors[] = 'Periods per day is not configured. Set up the bell schedule first in Timetable Settings.';
        }

        $hasDoubles = false;
        foreach ($data->klassSubjects as $ks) {
            if ((int) ($ks['doubles'] ?? 0) > 0) {
                $hasDoubles = true;
                break;
            }
        }
        if (!$hasDoubles) {
            foreach ($data->couplingGroups as $group) {
                if ((int) ($group['doubles'] ?? 0) > 0) {
                    $hasDoubles = true;
                    break;
                }
            }
        }
        if ($hasDoubles && empty($validDoubleStarts)) {
            $errors[] = 'Double-period allocations exist but no valid double start positions can be derived from current periods and breaks. Adjust break placement or reduce doubles.';
        }

        $availableSlots = $data->periodsPerDay * $data->cycleDays;

        // Check per-class allocation doesn't exceed available slots
        foreach ($data->klassAssignments as $klassId => $ksIds) {
            $totalPeriods = 0;
            $subjectBreakdown = [];
            foreach ($ksIds as $ksId) {
                $ks = $data->klassSubjects[$ksId] ?? null;
                if ($ks) {
                    $ksPeriods = $ks['singles'] + ($ks['doubles'] * 2) + ($ks['triples'] * 3);
                    $totalPeriods += $ksPeriods;
                    $subjectName = $data->subjectNames[$ks['subject_id']] ?? "Subject #{$ks['subject_id']}";
                    $subjectBreakdown[] = "{$subjectName}: {$ksPeriods}";
                }
            }
            if ($totalPeriods > $availableSlots) {
                $klassName = $data->klassNames[$klassId] ?? "Class #{$klassId}";
                $excess = $totalPeriods - $availableSlots;
                $errors[] = "{$klassName} has {$totalPeriods} allocated periods but only {$availableSlots} slots per cycle ({$data->periodsPerDay} periods/day × {$data->cycleDays} days). Over by {$excess} period(s). Breakdown: " . implode(', ', $subjectBreakdown) . ".";
            }
        }

        // Check teacher total doesn't exceed available minus unavailable
        foreach ($data->teacherAssignments as $teacherId => $ksIds) {
            $totalPeriods = 0;
            $classBreakdown = [];
            foreach ($ksIds as $ksId) {
                $ks = $data->klassSubjects[$ksId] ?? null;
                if ($ks) {
                    $ksPeriods = $ks['singles'] + ($ks['doubles'] * 2) + ($ks['triples'] * 3);
                    $totalPeriods += $ksPeriods;
                    $klassName = $data->klassNames[$ks['klass_id']] ?? "Class #{$ks['klass_id']}";
                    $subjectName = $data->subjectNames[$ks['subject_id']] ?? "Subject #{$ks['subject_id']}";
                    $classBreakdown[] = "{$subjectName} ({$klassName}): {$ksPeriods}";
                }
            }
            $unavailableCount = count($data->teacherUnavailability[$teacherId] ?? []);
            $teacherAvailable = $availableSlots - $unavailableCount;
            if ($totalPeriods > $teacherAvailable) {
                $teacherName = $data->teacherNames[$teacherId] ?? "Teacher #{$teacherId}";
                $excess = $totalPeriods - $teacherAvailable;
                $unavailNote = $unavailableCount > 0 ? " ({$unavailableCount} periods marked unavailable)" : '';
                $errors[] = "{$teacherName} has {$totalPeriods} teaching periods but only {$teacherAvailable} available slots{$unavailNote}. Over by {$excess} period(s). Assignments: " . implode(', ', $classBreakdown) . ".";
            }
        }

        // Check grade-level feasibility: core + coupled electives must fit in each class timetable.
        $gradeOptionalDemand = [];
        foreach ($data->couplingGroups as $group) {
            $gradeId = (int) ($group['grade_id'] ?? 0);
            if ($gradeId <= 0) {
                continue;
            }

            $periods = (int) ($group['singles'] ?? 0)
                + ((int) ($group['doubles'] ?? 0) * 2)
                + ((int) ($group['triples'] ?? 0) * 3);

            $gradeOptionalDemand[$gradeId] = ($gradeOptionalDemand[$gradeId] ?? 0) + $periods;
        }

        $gradeClassCore = [];
        foreach ($data->klassSubjects as $ks) {
            $gradeId = (int) ($ks['grade_id'] ?? 0);
            $klassId = (int) ($ks['klass_id'] ?? 0);
            if ($gradeId <= 0 || $klassId <= 0) {
                continue;
            }

            $corePeriods = (int) ($ks['singles'] ?? 0)
                + ((int) ($ks['doubles'] ?? 0) * 2)
                + ((int) ($ks['triples'] ?? 0) * 3);

            $gradeClassCore[$gradeId][$klassId] = ($gradeClassCore[$gradeId][$klassId] ?? 0) + $corePeriods;
        }

        foreach ($gradeClassCore as $gradeId => $classLoads) {
            $electivePeriods = (int) ($gradeOptionalDemand[$gradeId] ?? 0);
            if ($electivePeriods <= 0) {
                continue;
            }

            foreach ($classLoads as $klassId => $corePeriods) {
                $combined = (int) $corePeriods + $electivePeriods;
                if ($combined <= $availableSlots) {
                    continue;
                }

                $klassName = $data->klassNames[$klassId] ?? "Class #{$klassId}";
                $overBy = $combined - $availableSlots;
                $errors[] = "{$klassName} (grade {$gradeId}) is over capacity when coupling is applied: core {$corePeriods} + coupled electives {$electivePeriods} = {$combined}, but only {$availableSlots} slots are available per cycle. Reduce allocations by {$overBy} period(s) or lower elective coupling blocks.";
            }
        }

        // Check for duplicate venues within coupling groups.
        // Coupled electives run at the same time, so each must have a unique venue.
        foreach ($data->couplingGroups as $group) {
            $gradeId = (int) ($group['grade_id'] ?? 0);
            $gradeName = \App\Models\Grade::where('id', $gradeId)->value('name') ?? "Grade #{$gradeId}";
            $label = $group['label'] ?? 'unknown';
            $optSubjectIds = $group['optional_subject_ids'] ?? [];

            $venueUsage = []; // venue_id => [subject_id => subject_name]
            $missingVenue = [];
            foreach ($optSubjectIds as $osId) {
                $osId = (int) $osId;
                $info = $data->optionalSubjectMap[$osId] ?? null;
                if (!$info) {
                    continue;
                }

                $subjectId = (int) $info['subject_id'];
                $subjectName = $data->subjectNames[$subjectId] ?? "Subject #{$subjectId}";
                $venueId = (int) ($info['venue_id'] ?? 0);

                if ($venueId <= 0) {
                    $missingVenue[$subjectId] = $subjectName;
                    continue;
                }

                // Track unique subjects per venue (multiple streams of the same subject sharing a venue is fine)
                $venueUsage[$venueId][$subjectId] = $subjectName;
            }

            // Warn about missing venue assignments (deduplicated by subject)
            if (!empty($missingVenue)) {
                $subjectList = implode(', ', array_values($missingVenue));
                $errors[] = "Coupling group '{$label}' ({$gradeName}): the following electives have no venue assigned: {$subjectList}. Assign a venue to each optional subject so rooms are not double-booked.";
            }

            // Error only when distinct subjects share the same venue within the group
            foreach ($venueUsage as $venueId => $subjects) {
                if (count($subjects) > 1) {
                    $venueName = $data->venueNames[$venueId] ?? "Venue #{$venueId}";
                    $subjectList = implode(', ', array_values($subjects));
                    $errors[] = "Coupling group '{$label}' ({$gradeName}): multiple electives share the same venue ({$venueName}): {$subjectList}. Each coupled elective runs at the same time and must have its own unique venue.";
                }
            }
        }

        // Check that coupling group labels per grade don't exceed cycle days
        $gradeCouplingLabels = [];
        foreach ($data->couplingGroups as $group) {
            $gradeId = (int) ($group['grade_id'] ?? 0);
            $label = $group['label'] ?? '';
            if ($gradeId > 0 && $label !== '') {
                $gradeCouplingLabels[$gradeId][$label] = true;
            }
        }
        foreach ($gradeCouplingLabels as $gradeId => $labels) {
            if (count($labels) > $data->cycleDays) {
                $gradeName = \App\Models\Grade::where('id', $gradeId)->value('name') ?? "Grade #{$gradeId}";
                $errors[] = "{$gradeName} has " . count($labels) . " coupling group labels but only {$data->cycleDays} days in the cycle. Each label must be on a different day, so this is infeasible. Remove coupling groups or add cycle days.";
            }
        }

        return $errors;
    }

    /**
     * Convert the best chromosome into timetable_slots rows.
     */
    public function persistSolution(Timetable $timetable, GenerationResult $result, int $userId): void {
        $this->persistGenes($timetable, $result, $userId, skipConflicting: false);
    }

    /**
     * Persist only non-conflicting genes from a partial result.
     *
     * Uses the geneViolationMap to skip genes involved in hard violations.
     * The user can then manually fill the remaining gaps via the grid.
     */
    public function persistPartialSolution(Timetable $timetable, GenerationResult $result, int $userId): void {
        $this->persistGenes($timetable, $result, $userId, skipConflicting: true);
    }

    /**
     * Core persistence logic shared by full and partial saves.
     *
     * @param bool $skipConflicting If true, skip genes in geneViolationMap
     */
    private function persistGenes(Timetable $timetable, GenerationResult $result, int $userId, bool $skipConflicting): void {
        DB::transaction(function () use ($timetable, $result, $userId, $skipConflicting) {
            // Delete existing non-locked slots
            TimetableSlot::where('timetable_id', $timetable->id)
                ->where('is_locked', false)
                ->delete();

            $chromosome = $result->chromosome;
            $conflictMap = $skipConflicting ? $result->geneViolationMap : [];
            $slotsCreated = 0;
            $rows = [];
            $timestamp = now();

            foreach ($chromosome->genes as $i => $gene) {
                if ($gene->dayOfCycle === 0) {
                    continue; // Unassigned
                }

                // Skip genes with hard violations when doing partial save
                if (isset($conflictMap[$i])) {
                    continue;
                }

                $blockId = null;
                if ($gene->duration > 1) {
                    $blockId = Str::uuid()->toString();
                }

                for ($p = $gene->startPeriod; $p < $gene->startPeriod + $gene->duration; $p++) {
                    $rows[] = [
                        'timetable_id' => $timetable->id,
                        'klass_subject_id' => $gene->klassSubjectId ?: null,
                        'optional_subject_id' => $gene->optionalSubjectId,
                        'teacher_id' => $gene->teacherId ?: null,
                        'venue_id' => $gene->venueId ?: null,
                        'assistant_teacher_id' => $gene->assistantTeacherId ?: null,
                        'day_of_cycle' => $gene->dayOfCycle,
                        'period_number' => $p,
                        'duration' => $gene->duration,
                        'is_locked' => false,
                        'block_id' => $blockId,
                        'coupling_group_key' => $gene->couplingKey,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                    $slotsCreated++;

                    if (count($rows) >= 500) {
                        TimetableSlot::query()->insert($rows);
                        $rows = [];
                    }
                }
            }

            if (!empty($rows)) {
                TimetableSlot::query()->insert($rows);
            }

            app(TimetableIntegrityService::class)->forgetCachedAnalysis($timetable->id);

            // Audit log
            $action = $skipConflicting ? 'timetable_partially_generated' : 'timetable_generated';
            $desc = $skipConflicting
                ? "Partial generation: {$slotsCreated} slots placed, {$result->skippedCount} skipped due to conflicts, fitness " . number_format($result->fitness, 4)
                : "Auto-generated timetable: {$result->totalSlots} slots, fitness " . number_format($result->fitness, 4) . ", {$result->generations} generations";

            if (!$skipConflicting && $result->hardViolationCount > 0) {
                $desc .= " ({$result->hardViolationCount} conflict(s) — saved for review)";
            }

            TimetableAuditLog::log(
                $timetable,
                $action,
                $desc,
                null,
                [
                    'generations' => $result->generations,
                    'fitness' => $result->fitness,
                    'total_slots' => $result->totalSlots,
                    'slots_placed' => $slotsCreated,
                    'slots_skipped' => $result->skippedCount,
                    'hard_violations' => $result->hardViolationCount,
                    'population_size' => $this->populationSize,
                ],
                $userId
            );
        });
    }
}

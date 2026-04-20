<?php

namespace App\Services\Timetable\Generation;

use App\Services\Timetable\Support\BlockPlacementRules;

/**
 * Evaluates chromosome fitness using only in-memory data from GenerationData.
 *
 * Scores range from 0.0 (worst) to 1.0 (perfect). Hard violations carry heavy penalties
 * (weight 100), coupling misalignment moderate penalties (weight 50), and soft constraint
 * violations lighter penalties (weights 1-5).
 *
 * Performance-critical: called for every chromosome in every generation.
 * Uses string-keyed hash maps for O(1) conflict lookups.
 */
class FitnessEvaluator {
    /** Pre-built occupancy maps for locked slots. */
    private array $lockedTeacherOccupancy = [];
    private array $lockedKlassOccupancy = [];
    private array $lockedVenueOccupancy = [];
    private array $lockedAssistantTeacherOccupancy = [];
    private array $lockedGradeCoreOccupancy = [];
    private array $lockedGradeOptionalOccupancy = [];
    private array $breakSet = [];
    private array $unavailabilitySet = [];
    private array $preferenceSets = [];
    private array $periodRestrictionSets = [];
    private array $validDoubleStartSet = [];

    public function __construct(
        private readonly GenerationData $data,
    ) {
        $this->breakSet = array_flip($this->data->breakAfterPeriods);
        $validDoubleStarts = $this->data->validDoubleStartPeriods;
        if (empty($validDoubleStarts)) {
            $validDoubleStarts = BlockPlacementRules::computeValidDoubleStartPeriods(
                $this->data->periodsPerDay,
                $this->data->breakAfterPeriods
            );
        }
        $this->validDoubleStartSet = array_fill_keys(array_map('intval', $validDoubleStarts), true);

        // Pre-compute locked slot occupancy maps
        foreach ($this->data->lockedSlots as $slot) {
            $teacherId = $slot['teacher_id'];
            $klassId = $slot['klass_id'];
            $venueId = (int) ($slot['venue_id'] ?? 0);
            $assistantTeacherId = (int) ($slot['assistant_teacher_id'] ?? 0);
            $gradeId = (int) ($slot['grade_id'] ?? 0);
            $isOptional = (bool) ($slot['is_optional'] ?? (($slot['optional_subject_id'] ?? null) !== null));
            $day = $slot['day_of_cycle'];
            $duration = $slot['duration'] ?? 1;

            for ($p = $slot['period_number']; $p < $slot['period_number'] + $duration; $p++) {
                if ($teacherId) {
                    $this->lockedTeacherOccupancy["{$teacherId}:{$day}:{$p}"] = true;
                }
                if ($klassId) {
                    $this->lockedKlassOccupancy["{$klassId}:{$day}:{$p}"] = true;
                }
                if ($venueId > 0) {
                    $this->lockedVenueOccupancy["{$venueId}:{$day}:{$p}"] = true;
                }
                if ($assistantTeacherId > 0) {
                    $this->lockedAssistantTeacherOccupancy["{$assistantTeacherId}:{$day}:{$p}"] = true;
                }
                if ($gradeId > 0) {
                    $gKey = "{$gradeId}:{$day}:{$p}";
                    if ($isOptional) {
                        $this->lockedGradeOptionalOccupancy[$gKey] = true;
                    } else {
                        $this->lockedGradeCoreOccupancy[$gKey] = true;
                    }
                }
            }
        }

        foreach ($this->data->teacherUnavailability as $tid => $slots) {
            foreach ($slots as $slot) {
                $this->unavailabilitySet["{$tid}:{$slot['day_of_cycle']}:{$slot['period_number']}"] = true;
            }
        }

        foreach ($this->data->teacherPreferences as $tid => $pref) {
            if (!empty($pref['preferred_periods'])) {
                $this->preferenceSets[$tid] = array_flip($pref['preferred_periods']);
            }
        }

        // Pre-compute period restriction allowed sets for O(1) lookup
        foreach ($this->data->periodRestrictions as $subjectId => $info) {
            $allowedPeriods = $info['allowed_periods'] ?? [];
            if (!empty($allowedPeriods)) {
                $this->periodRestrictionSets[$subjectId] = array_flip($allowedPeriods);
            }
        }
    }

    /**
     * Evaluate a chromosome and return its fitness score.
     *
     * @param Chromosome $chromosome The chromosome to evaluate (modified: hardViolationCount set)
     * @return float Fitness score from 0.0 to 1.0
     */
    public function evaluate(Chromosome $chromosome): float {
        $totalPenalty = 0;
        $hardViolationCount = 0;
        $genes = $chromosome->genes;

        // Build occupancy maps from chromosome genes (single pass)
        [$teacherOccupancy, $klassOccupancy, $venueOccupancy,
         $assistantTeacherOccupancy, $gradeCoreOccupancy, $gradeOptionalOccupancy] = $this->buildAllOccupancyMaps($chromosome);

        // Track subject-per-class-per-day lesson counts for spread check
        $subjectDayLessonCounts = [];
        // Track teacher periods per day for consecutive check
        $teacherDayPeriods = [];
        // Track subject presence and positions per day/class for subject pair checks
        $subjectDayPresence = [];
        $subjectPeriodPositions = [];
        // Subject-to-klass index for O(1) pair lookups
        $subjectKlassIndex = [];

        foreach ($genes as $i => $gene) {
            if ($gene->dayOfCycle === 0) {
                continue; // Unassigned gene, skip
            }

            $day = $gene->dayOfCycle;
            $teacherId = $gene->teacherId;
            $klassId = $gene->klassId;
            $subjectId = $gene->subjectId;
            $duration = $gene->duration;
            $subjectDayKey = null;
            if ($subjectId && $klassId) {
                $subjectDayKey = "{$subjectId}:{$klassId}:{$day}";
                $subjectDayLessonCounts[$subjectDayKey] = ($subjectDayLessonCounts[$subjectDayKey] ?? 0) + 1;
                $subjectDayPresence[$subjectDayKey] = true;
                $subjectKlassIndex[$subjectId][$klassId] = true;
            }

            for ($p = $gene->startPeriod; $p < $gene->startPeriod + $duration; $p++) {
                // ---- Hard: Period out of bounds ----
                if ($p > $this->data->periodsPerDay || $p < 1) {
                    $totalPenalty += 100;
                    $hardViolationCount++;
                    continue;
                }

                // ---- Hard: Core vs coupled-elective overlap (same grade/day/period) ----
                if ($gene->gradeId > 0) {
                    $gKey = "{$gene->gradeId}:{$day}:{$p}";
                    if ($gene->optionalSubjectId !== null) {
                        if (isset($gradeCoreOccupancy[$gKey]) && $gradeCoreOccupancy[$gKey] !== $i) {
                            $totalPenalty += 100;
                            $hardViolationCount++;
                        }
                        if (isset($this->lockedGradeCoreOccupancy[$gKey])) {
                            $totalPenalty += 100;
                            $hardViolationCount++;
                        }
                    } elseif ($gene->klassId > 0) {
                        if (isset($gradeOptionalOccupancy[$gKey]) && $gradeOptionalOccupancy[$gKey] !== $i) {
                            $totalPenalty += 100;
                            $hardViolationCount++;
                        }
                        if (isset($this->lockedGradeOptionalOccupancy[$gKey])) {
                            $totalPenalty += 100;
                            $hardViolationCount++;
                        }
                    }
                }

                // ---- Hard: Teacher double-booking ----
                if ($teacherId) {
                    $tKey = "{$teacherId}:{$day}:{$p}";
                    if (isset($teacherOccupancy[$tKey]) && $teacherOccupancy[$tKey] !== $i) {
                        $totalPenalty += 100;
                        $hardViolationCount++;
                    }
                    // Also check locked slot collisions
                    if (isset($this->lockedTeacherOccupancy[$tKey])) {
                        $totalPenalty += 100;
                        $hardViolationCount++;
                    }
                }

                // ---- Hard: Class double-booking ----
                if ($klassId) {
                    $cKey = "{$klassId}:{$day}:{$p}";
                    if (isset($klassOccupancy[$cKey]) && $klassOccupancy[$cKey] !== $i) {
                        $totalPenalty += 100;
                        $hardViolationCount++;
                    }
                    if (isset($this->lockedKlassOccupancy[$cKey])) {
                        $totalPenalty += 100;
                        $hardViolationCount++;
                    }
                }

                // ---- Hard: Venue double-booking ----
                if ($gene->venueId > 0) {
                    $vKey = "{$gene->venueId}:{$day}:{$p}";
                    if (isset($venueOccupancy[$vKey]) && $venueOccupancy[$vKey] !== $i) {
                        $totalPenalty += 100;
                        $hardViolationCount++;
                    }
                    if (isset($this->lockedVenueOccupancy[$vKey])) {
                        $totalPenalty += 100;
                        $hardViolationCount++;
                    }
                }

                // ---- Hard: Assistant teacher double-booking ----
                if ($gene->assistantTeacherId > 0) {
                    $aKey = "{$gene->assistantTeacherId}:{$day}:{$p}";
                    if (isset($assistantTeacherOccupancy[$aKey]) && $assistantTeacherOccupancy[$aKey] !== $i) {
                        $totalPenalty += 100;
                        $hardViolationCount++;
                    }
                    if (isset($this->lockedAssistantTeacherOccupancy[$aKey])) {
                        $totalPenalty += 100;
                        $hardViolationCount++;
                    }
                }

                // ---- Hard: Teacher unavailability ----
                if ($teacherId && isset($this->unavailabilitySet["{$teacherId}:{$day}:{$p}"])) {
                    $totalPenalty += 100;
                    $hardViolationCount++;
                }

                // ---- Soft: Teacher preference ----
                if ($teacherId && isset($this->preferenceSets[$teacherId]) && !isset($this->preferenceSets[$teacherId][$p])) {
                    $totalPenalty += 5;
                }

                // Track for spread + consecutive checks
                if ($subjectDayKey !== null) {
                    $subjectPeriodPositions[$subjectDayKey][] = $p;
                }
                if ($teacherId) {
                    $teacherDayPeriods["{$teacherId}:{$day}"][] = $p;
                }

                // ---- Soft: Period restriction violated (weight 3-5) ----
                if ($subjectId && isset($this->periodRestrictionSets[$subjectId]) && !isset($this->periodRestrictionSets[$subjectId][$p])) {
                    $weight = (int) ($this->data->periodRestrictions[$subjectId]['restriction'] === 'fixed_period' ? 5 : ($this->data->periodRestrictions[$subjectId]['restriction'] === 'afternoon_only' ? 3 : 4));
                    $totalPenalty += $weight;
                }
            }

            // ---- Hard: Block spans break ----
            if ($duration > 1) {
                for ($p = $gene->startPeriod; $p < $gene->startPeriod + $duration - 1; $p++) {
                    if (isset($this->breakSet[$p])) {
                        $totalPenalty += 100;
                        $hardViolationCount++;
                    }
                }
            }

            if (BlockPlacementRules::isMisalignedDoubleStart($gene->startPeriod, $duration, $this->validDoubleStartSet)) {
                $totalPenalty += 100;
                $hardViolationCount++;
            }
        }

        // ---- Coupling group mismatch (weight 50) ----
        $couplingGroups = [];
        foreach ($genes as $gene) {
            if ($gene->couplingKey !== null && $gene->dayOfCycle > 0) {
                $couplingGroups[$gene->couplingKey][] = $gene;
            }
        }
        foreach ($couplingGroups as $key => $grouped) {
            if (count($grouped) <= 1) {
                continue;
            }
            // Find majority position
            $positions = [];
            foreach ($grouped as $g) {
                $posKey = "{$g->dayOfCycle}:{$g->startPeriod}";
                $positions[$posKey] = ($positions[$posKey] ?? 0) + 1;
            }
            arsort($positions);
            $majorityPos = array_key_first($positions);
            foreach ($grouped as $g) {
                $posKey = "{$g->dayOfCycle}:{$g->startPeriod}";
                if ($posKey !== $majorityPos) {
                    $totalPenalty += 50;
                    $hardViolationCount++;
                }
            }
        }

        // ---- Hard: Same-grade coupling groups must be on different days (weight 100) ----
        $couplingKeyDay = [];
        foreach ($couplingGroups as $key => $grouped) {
            if (empty($grouped)) {
                continue;
            }
            $dayCounts = [];
            foreach ($grouped as $g) {
                $dayCounts[$g->dayOfCycle] = ($dayCounts[$g->dayOfCycle] ?? 0) + 1;
            }
            arsort($dayCounts);
            $couplingKeyDay[$key] = (int) array_key_first($dayCounts);
        }
        $gradeLabelDays = [];
        foreach ($couplingKeyDay as $key => $day) {
            if (preg_match('/^cg_(\d+)_(.+)_[sdt]\d+$/', $key, $m)) {
                $gradeLabelDays[(int) $m[1]][$m[2]][$day] = true;
            }
        }
        foreach ($gradeLabelDays as $labels) {
            $labelNames = array_keys($labels);
            $labelCount = count($labelNames);
            for ($li = 0; $li < $labelCount; $li++) {
                for ($lj = $li + 1; $lj < $labelCount; $lj++) {
                    $sharedCount = count(array_intersect_key($labels[$labelNames[$li]], $labels[$labelNames[$lj]]));
                    $totalPenalty += 100 * $sharedCount;
                    $hardViolationCount += $sharedCount;
                }
            }
        }

        // ---- Soft: Subject spread exceeded (weight 3) ----
        foreach ($subjectDayLessonCounts as $key => $count) {
            [$subjectId] = explode(':', $key);
            $subjectId = (int) $subjectId;
            if (isset($this->data->subjectSpreads[$subjectId])) {
                $max = (int) ($this->data->subjectSpreads[$subjectId]['max_lessons_per_day'] ?? 0);
                if ($count > $max) {
                    $totalPenalty += 3 * ($count - $max);
                }
            }
        }

        // ---- Soft: Subject pair rules (weight 3-4) ----
        foreach ($this->data->subjectPairs as $pair) {
            $sA = (int) $pair['subject_id_a'];
            $sB = (int) $pair['subject_id_b'];
            $pairKlassId = $pair['klass_id'] ?? null;
            $rule = $pair['rule'];

            // Determine which class IDs to check
            $klassIdsToCheck = [];
            if ($pairKlassId !== null) {
                $klassIdsToCheck[] = (int) $pairKlassId;
            } else {
                // Direct hash lookup: O(1) per subject instead of scanning all keys.
                // For must_same_day/must_follow, use intersection — only classes teaching
                // BOTH subjects should be checked, otherwise false penalties are generated.
                // For not_same_day/not_consecutive, union is safe since those rules only
                // fire when both subjects are present on the same day.
                $klassIdsForA = array_keys($subjectKlassIndex[$sA] ?? []);
                $klassIdsForB = array_keys($subjectKlassIndex[$sB] ?? []);
                if ($rule === 'must_same_day' || $rule === 'must_follow') {
                    $klassIdsToCheck = array_values(array_intersect($klassIdsForA, $klassIdsForB));
                } else {
                    $klassIdsToCheck = array_values(array_unique(array_merge($klassIdsForA, $klassIdsForB)));
                }
            }

            foreach ($klassIdsToCheck as $kid) {
                for ($d = 1; $d <= $this->data->cycleDays; $d++) {
                    $keyA = "{$sA}:{$kid}:{$d}";
                    $keyB = "{$sB}:{$kid}:{$d}";
                    $aPresent = isset($subjectDayPresence[$keyA]);
                    $bPresent = isset($subjectDayPresence[$keyB]);

                    switch ($rule) {
                        case 'not_same_day':
                            if ($aPresent && $bPresent) {
                                $totalPenalty += 4;
                            }
                            break;

                        case 'must_same_day':
                            if ($aPresent !== $bPresent) {
                                $totalPenalty += 4;
                            }
                            break;

                        case 'not_consecutive':
                            if ($aPresent && $bPresent) {
                                $periodsA = $subjectPeriodPositions[$keyA] ?? [];
                                $periodsB = $subjectPeriodPositions[$keyB] ?? [];
                                foreach ($periodsA as $pA) {
                                    foreach ($periodsB as $pB) {
                                        if (abs($pA - $pB) === 1) {
                                            $totalPenalty += 3;
                                        }
                                    }
                                }
                            }
                            break;

                        case 'must_follow':
                            if ($aPresent && $bPresent) {
                                $periodsA = $subjectPeriodPositions[$keyA] ?? [];
                                $periodsB = $subjectPeriodPositions[$keyB] ?? [];
                                $adjacent = false;
                                foreach ($periodsA as $pA) {
                                    foreach ($periodsB as $pB) {
                                        if (abs($pA - $pB) === 1) {
                                            $adjacent = true;
                                            break 2;
                                        }
                                    }
                                }
                                if (!$adjacent) {
                                    $totalPenalty += 4;
                                }
                            } elseif ($aPresent !== $bPresent) {
                                $totalPenalty += 4;
                            }
                            break;
                    }
                }
            }
        }

        // ---- Soft: Consecutive teaching limit exceeded (weight 3) ----
        foreach ($teacherDayPeriods as $key => $periods) {
            [$teacherId] = explode(':', $key);
            $teacherId = (int) $teacherId;

            $max = null;
            if (isset($this->data->consecutiveLimits[$teacherId])) {
                $max = $this->data->consecutiveLimits[$teacherId]['max_consecutive_periods'];
            } elseif (isset($this->data->consecutiveLimits['global'])) {
                $max = $this->data->consecutiveLimits['global']['max_consecutive_periods'];
            }

            if ($max !== null) {
                sort($periods);
                $periods = array_unique($periods);
                $longestRun = $this->findLongestConsecutiveRun($periods);
                if ($longestRun > $max) {
                    $totalPenalty += 3 * ($longestRun - $max);
                }
            }
        }

        // ---- Soft: Uneven day distribution (weight 1) ----
        $klassDayCounts = [];
        foreach ($genes as $gene) {
            if ($gene->dayOfCycle > 0 && $gene->klassId > 0) {
                $klassDayCounts[$gene->klassId][$gene->dayOfCycle] =
                    ($klassDayCounts[$gene->klassId][$gene->dayOfCycle] ?? 0) + 1;
            }
        }
        foreach ($klassDayCounts as $klassId => $dayCounts) {
            $values = array_values($dayCounts);
            if (count($values) > 1) {
                $stddev = $this->calculateStdDev($values);
                if ($stddev > 1.5) {
                    $totalPenalty += 1;
                }
            }
        }

        // Calculate fitness
        $maxPenalty = max(1, count($genes) * 100);
        $fitness = max(0.0, 1.0 - ($totalPenalty / $maxPenalty));

        $chromosome->hardViolationCount = $hardViolationCount;

        return $fitness;
    }

    /**
     * Generate a detailed, human-readable violation report for the final solution.
     *
     * Includes a summary header, descriptive messages identifying both conflicting
     * entities, and the specific day/period/subject involved.
     *
     * Called once at the end, so performance is less critical.
     *
     * @return string[]
     */
    public function getViolationReport(Chromosome $chromosome): array {
        $violations = [];
        $genes = $chromosome->genes;

        // Counters for summary
        $counts = [
            'teacher_double' => 0,
            'class_double' => 0,
            'venue_double' => 0,
            'assistant_double' => 0,
            'unavailable' => 0,
            'out_of_bounds' => 0,
            'break_span' => 0,
            'double_misalignment' => 0,
            'locked_conflict' => 0,
            'coupling' => 0,
            'coupling_day_conflict' => 0,
            'core_elective_overlap' => 0,
            'subject_pair' => 0,
            'period_restriction' => 0,
        ];

        // Build occupancy maps (single pass)
        [$teacherOccupancy, $klassOccupancy, $venueOccupancy,
         $assistantTeacherOccupancy, $gradeCoreOccupancy, $gradeOptionalOccupancy] = $this->buildAllOccupancyMaps($chromosome);

        foreach ($genes as $i => $gene) {
            if ($gene->dayOfCycle === 0) {
                continue;
            }

            $teacherName = $this->data->teacherNames[$gene->teacherId] ?? "Teacher #{$gene->teacherId}";
            $klassName = $this->data->klassNames[$gene->klassId] ?? "Class #{$gene->klassId}";
            $subjectName = $this->data->subjectNames[$gene->subjectId] ?? "Subject #{$gene->subjectId}";
            $entityLabel = $this->describeGene($gene);
            $day = $gene->dayOfCycle;
            $blockLabel = $gene->duration > 1 ? " ({$gene->duration}-period block)" : '';

            for ($p = $gene->startPeriod; $p < $gene->startPeriod + $gene->duration; $p++) {
                // Period out of bounds
                if ($p > $this->data->periodsPerDay || $p < 1) {
                    $counts['out_of_bounds']++;
                    $violations[] = "Period {$p} is out of bounds (max {$this->data->periodsPerDay}) — {$subjectName} for {$klassName} taught by {$teacherName} on Day {$day}. Check block allocation sizes.";
                    continue;
                }

                // Core vs coupled-elective overlap (same grade/day/period)
                if ($gene->gradeId > 0) {
                    $gKey = "{$gene->gradeId}:{$day}:{$p}";
                    if ($gene->optionalSubjectId !== null) {
                        if (isset($gradeCoreOccupancy[$gKey]) && $gradeCoreOccupancy[$gKey] !== $i) {
                            $otherGene = $genes[$gradeCoreOccupancy[$gKey]];
                            $counts['core_elective_overlap']++;
                            $violations[] = "Coupled elective '{$subjectName}' overlaps core lesson {$this->describeGene($otherGene)} on Grade {$gene->gradeId}, Day {$day}, Period {$p}. Core and electives cannot run at the same time.";
                        }
                        if (isset($this->lockedGradeCoreOccupancy[$gKey])) {
                            $counts['locked_conflict']++;
                            $violations[] = "Coupled elective '{$subjectName}' conflicts with a locked core slot on Grade {$gene->gradeId}, Day {$day}, Period {$p}.";
                        }
                    } elseif ($gene->klassId > 0) {
                        if (isset($gradeOptionalOccupancy[$gKey]) && $gradeOptionalOccupancy[$gKey] !== $i) {
                            $otherGene = $genes[$gradeOptionalOccupancy[$gKey]];
                            $counts['core_elective_overlap']++;
                            $violations[] = "Core lesson {$this->describeGene($gene)} overlaps coupled elective {$this->describeGene($otherGene)} on Grade {$gene->gradeId}, Day {$day}, Period {$p}.";
                        }
                        if (isset($this->lockedGradeOptionalOccupancy[$gKey])) {
                            $counts['locked_conflict']++;
                            $violations[] = "Core lesson {$this->describeGene($gene)} conflicts with a locked elective slot on Grade {$gene->gradeId}, Day {$day}, Period {$p}.";
                        }
                    }
                }

                // Teacher double-booking
                if ($gene->teacherId) {
                    $tKey = "{$gene->teacherId}:{$day}:{$p}";
                    if (isset($teacherOccupancy[$tKey]) && $teacherOccupancy[$tKey] !== $i) {
                        $otherGene = $genes[$teacherOccupancy[$tKey]];
                        $otherSubject = $this->data->subjectNames[$otherGene->subjectId] ?? "Subject #{$otherGene->subjectId}";
                        $otherKlass = $this->data->klassNames[$otherGene->klassId] ?? "Class #{$otherGene->klassId}";
                        $counts['teacher_double']++;
                        $violations[] = "{$teacherName} is double-booked on Day {$day}, Period {$p}: teaches {$subjectName} ({$klassName}) and {$otherSubject} ({$otherKlass}) at the same time.";
                    }
                    // Locked teacher slot collision
                    if (isset($this->lockedTeacherOccupancy[$tKey])) {
                        $counts['locked_conflict']++;
                        $violations[] = "{$teacherName} teaching {$subjectName} ({$klassName}) on Day {$day}, Period {$p} conflicts with a manually locked slot.";
                    }
                }

                // Class double-booking
                if ($gene->klassId) {
                    $cKey = "{$gene->klassId}:{$day}:{$p}";
                    if (isset($klassOccupancy[$cKey]) && $klassOccupancy[$cKey] !== $i) {
                        $otherGene = $genes[$klassOccupancy[$cKey]];
                        $otherSubject = $this->data->subjectNames[$otherGene->subjectId] ?? "Subject #{$otherGene->subjectId}";
                        $otherTeacher = $this->data->teacherNames[$otherGene->teacherId] ?? "Teacher #{$otherGene->teacherId}";
                        $counts['class_double']++;
                        $violations[] = "{$klassName} has two subjects at the same time on Day {$day}, Period {$p}: {$subjectName} ({$teacherName}) and {$otherSubject} ({$otherTeacher}).";
                    }
                    // Locked class slot collision
                    if (isset($this->lockedKlassOccupancy[$cKey])) {
                        $counts['locked_conflict']++;
                        $violations[] = "{$klassName} has {$subjectName} ({$teacherName}) on Day {$day}, Period {$p} which conflicts with a manually locked slot.";
                    }
                }

                // Teacher unavailability
                if ($gene->teacherId && isset($this->unavailabilitySet["{$gene->teacherId}:{$day}:{$p}"])) {
                    $counts['unavailable']++;
                    $violations[] = "{$teacherName} is marked as unavailable on Day {$day}, Period {$p}, but is scheduled to teach {$subjectName} ({$klassName}).";
                }

                // Period restriction
                if ($gene->subjectId && isset($this->periodRestrictionSets[$gene->subjectId]) && !isset($this->periodRestrictionSets[$gene->subjectId][$p])) {
                    $counts['period_restriction']++;
                    $restriction = $this->data->periodRestrictions[$gene->subjectId]['restriction'] ?? 'unknown';
                    $violations[] = "{$subjectName} ({$klassName}) is placed at Period {$p} on Day {$day}, violating '{$restriction}' restriction.";
                }

                // Venue double-booking
                if ($gene->venueId > 0) {
                    $vKey = "{$gene->venueId}:{$day}:{$p}";
                    if (isset($venueOccupancy[$vKey]) && $venueOccupancy[$vKey] !== $i) {
                        $otherGene = $genes[$venueOccupancy[$vKey]];
                        $otherSubject = $this->data->subjectNames[$otherGene->subjectId] ?? "Subject #{$otherGene->subjectId}";
                        $venueName = $this->data->venueNames[$gene->venueId] ?? "Venue #{$gene->venueId}";
                        $counts['venue_double']++;
                        $violations[] = "{$venueName} is double-booked on Day {$day}, Period {$p}: {$entityLabel} and {$otherSubject} ({$this->describeGene($otherGene)}).";
                    }
                    if (isset($this->lockedVenueOccupancy[$vKey])) {
                        $venueName = $this->data->venueNames[$gene->venueId] ?? "Venue #{$gene->venueId}";
                        $counts['locked_conflict']++;
                        $violations[] = "{$venueName} for {$entityLabel} on Day {$day}, Period {$p} conflicts with a locked slot.";
                    }
                }

                // Assistant teacher double-booking
                if ($gene->assistantTeacherId > 0) {
                    $aKey = "{$gene->assistantTeacherId}:{$day}:{$p}";
                    if (isset($assistantTeacherOccupancy[$aKey]) && $assistantTeacherOccupancy[$aKey] !== $i) {
                        $otherGene = $genes[$assistantTeacherOccupancy[$aKey]];
                        $otherSubject = $this->data->subjectNames[$otherGene->subjectId] ?? "Subject #{$otherGene->subjectId}";
                        $assistantName = $this->data->teacherNames[$gene->assistantTeacherId] ?? "Teacher #{$gene->assistantTeacherId}";
                        $counts['assistant_double']++;
                        $violations[] = "Assistant teacher {$assistantName} is double-booked on Day {$day}, Period {$p}: {$entityLabel} and {$otherSubject} ({$this->describeGene($otherGene)}).";
                    }
                    if (isset($this->lockedAssistantTeacherOccupancy[$aKey])) {
                        $assistantName = $this->data->teacherNames[$gene->assistantTeacherId] ?? "Teacher #{$gene->assistantTeacherId}";
                        $counts['locked_conflict']++;
                        $violations[] = "Assistant teacher {$assistantName} for {$entityLabel} on Day {$day}, Period {$p} conflicts with a locked slot.";
                    }
                }
            }

            // Block spans break
            if ($gene->duration > 1) {
                for ($p = $gene->startPeriod; $p < $gene->startPeriod + $gene->duration - 1; $p++) {
                    if (isset($this->breakSet[$p])) {
                        $counts['break_span']++;
                        $violations[] = "A {$gene->duration}-period block for {$subjectName} ({$klassName}, {$teacherName}) on Day {$day} starting at Period {$gene->startPeriod} spans across the break after Period {$p}. Double/triple blocks cannot cross breaks.";
                    }
                }
            }

            if (BlockPlacementRules::isMisalignedDoubleStart($gene->startPeriod, $gene->duration, $this->validDoubleStartSet)) {
                $counts['double_misalignment']++;
                $validStartsStr = implode(', ', array_keys($this->validDoubleStartSet));
                $violations[] = "Double block for {$subjectName} ({$klassName}, {$teacherName}) on Day {$day} starts at Period {$gene->startPeriod} and is misaligned. Valid double starts: {$validStartsStr}.";
            }
        }

        // Coupling misalignment
        $couplingGroups = [];
        foreach ($genes as $gene) {
            if ($gene->couplingKey !== null && $gene->dayOfCycle > 0) {
                $couplingGroups[$gene->couplingKey][] = $gene;
            }
        }
        foreach ($couplingGroups as $key => $grouped) {
            $positions = [];
            foreach ($grouped as $g) {
                $positions["{$g->dayOfCycle}:{$g->startPeriod}"][] = $g;
            }
            if (count($positions) > 1) {
                $counts['coupling']++;
                $positionDescriptions = [];
                foreach ($positions as $posKey => $posGenes) {
                    [$posDay, $posPeriod] = explode(':', $posKey);
                    $teachers = array_map(
                        fn($g) => $this->data->teacherNames[$g->teacherId] ?? "Teacher #{$g->teacherId}",
                        $posGenes
                    );
                    $positionDescriptions[] = "Day {$posDay} Period {$posPeriod} (" . implode(', ', array_unique($teachers)) . ")";
                }
                $violations[] = "Optional subject group '{$key}' must be scheduled at the same time, but members are split across: " . implode(' vs ', $positionDescriptions) . ".";
            }
        }

        // Coupling day conflict: same-grade labels sharing a day
        $couplingKeyDayReport = [];
        foreach ($couplingGroups as $key => $grouped) {
            if (empty($grouped)) {
                continue;
            }
            $dayCounts = [];
            foreach ($grouped as $g) {
                $dayCounts[$g->dayOfCycle] = ($dayCounts[$g->dayOfCycle] ?? 0) + 1;
            }
            arsort($dayCounts);
            $couplingKeyDayReport[$key] = (int) array_key_first($dayCounts);
        }
        $gradeLabelDaysReport = [];
        foreach ($couplingKeyDayReport as $key => $day) {
            if (preg_match('/^cg_(\d+)_(.+)_[sdt]\d+$/', $key, $m)) {
                $gradeLabelDaysReport[(int) $m[1]][$m[2]][$day] = true;
            }
        }
        foreach ($gradeLabelDaysReport as $gradeId => $labels) {
            $labelNames = array_keys($labels);
            $labelCount = count($labelNames);
            for ($li = 0; $li < $labelCount; $li++) {
                for ($lj = $li + 1; $lj < $labelCount; $lj++) {
                    $sharedDays = array_keys(array_intersect_key($labels[$labelNames[$li]], $labels[$labelNames[$lj]]));
                    foreach ($sharedDays as $sharedDay) {
                        $counts['coupling_day_conflict']++;
                        $violations[] = "Coupling groups '{$labelNames[$li]}' and '{$labelNames[$lj]}' in grade {$gradeId} are both scheduled on Day {$sharedDay}. Different coupling groups within the same grade must be on different days.";
                    }
                }
            }
        }

        // Subject pair violations
        if (!empty($this->data->subjectPairs)) {
            $spPresence = [];
            $spPositions = [];
            foreach ($genes as $gene) {
                if ($gene->dayOfCycle === 0 || !$gene->subjectId || !$gene->klassId) {
                    continue;
                }
                $day = $gene->dayOfCycle;
                $spKey = "{$gene->subjectId}:{$gene->klassId}:{$day}";
                $spPresence[$spKey] = true;
                for ($p = $gene->startPeriod; $p < $gene->startPeriod + $gene->duration; $p++) {
                    $spPositions[$spKey][] = $p;
                }
            }

            foreach ($this->data->subjectPairs as $pair) {
                $sA = (int) $pair['subject_id_a'];
                $sB = (int) $pair['subject_id_b'];
                $pairKlassId = $pair['klass_id'] ?? null;
                $rule = $pair['rule'];
                $nameA = $this->data->subjectNames[$sA] ?? "Subject #{$sA}";
                $nameB = $this->data->subjectNames[$sB] ?? "Subject #{$sB}";

                $klassIds = [];
                if ($pairKlassId !== null) {
                    $klassIds[] = (int) $pairKlassId;
                } else {
                    // Build per-subject class lists from presence keys
                    $klassIdsForA = [];
                    $klassIdsForB = [];
                    foreach ($spPresence as $presKey => $_) {
                        $parts = explode(':', $presKey);
                        $sid = (int) $parts[0];
                        $kid = (int) $parts[1];
                        if ($sid === $sA && !in_array($kid, $klassIdsForA, true)) {
                            $klassIdsForA[] = $kid;
                        }
                        if ($sid === $sB && !in_array($kid, $klassIdsForB, true)) {
                            $klassIdsForB[] = $kid;
                        }
                    }
                    // For must_same_day/must_follow, only check classes teaching BOTH subjects
                    if ($rule === 'must_same_day' || $rule === 'must_follow') {
                        $klassIds = array_values(array_intersect($klassIdsForA, $klassIdsForB));
                    } else {
                        $klassIds = array_values(array_unique(array_merge($klassIdsForA, $klassIdsForB)));
                    }
                }

                foreach ($klassIds as $kid) {
                    $klassName = $this->data->klassNames[$kid] ?? "Class #{$kid}";
                    for ($d = 1; $d <= $this->data->cycleDays; $d++) {
                        $kA = "{$sA}:{$kid}:{$d}";
                        $kB = "{$sB}:{$kid}:{$d}";
                        $aP = isset($spPresence[$kA]);
                        $bP = isset($spPresence[$kB]);

                        switch ($rule) {
                            case 'not_same_day':
                                if ($aP && $bP) {
                                    $counts['subject_pair']++;
                                    $violations[] = "{$nameA} and {$nameB} for {$klassName} are both on Day {$d} (rule: not same day).";
                                }
                                break;
                            case 'must_same_day':
                                if ($aP !== $bP) {
                                    $counts['subject_pair']++;
                                    $violations[] = "{$nameA} and {$nameB} for {$klassName}: one is on Day {$d} but the other isn't (rule: must be same day).";
                                }
                                break;
                            case 'not_consecutive':
                                if ($aP && $bP) {
                                    $posA = $spPositions[$kA] ?? [];
                                    $posB = $spPositions[$kB] ?? [];
                                    foreach ($posA as $pA) {
                                        foreach ($posB as $pB) {
                                            if (abs($pA - $pB) === 1) {
                                                $counts['subject_pair']++;
                                                $violations[] = "{$nameA} (P{$pA}) and {$nameB} (P{$pB}) are back-to-back for {$klassName} on Day {$d} (rule: not consecutive).";
                                            }
                                        }
                                    }
                                }
                                break;
                            case 'must_follow':
                                if ($aP && $bP) {
                                    $posA = $spPositions[$kA] ?? [];
                                    $posB = $spPositions[$kB] ?? [];
                                    $adjacent = false;
                                    foreach ($posA as $pA) {
                                        foreach ($posB as $pB) {
                                            if (abs($pA - $pB) === 1) {
                                                $adjacent = true;
                                                break 2;
                                            }
                                        }
                                    }
                                    if (!$adjacent) {
                                        $counts['subject_pair']++;
                                        $violations[] = "{$nameA} and {$nameB} for {$klassName} on Day {$d} are not adjacent (rule: must follow).";
                                    }
                                } elseif ($aP !== $bP) {
                                    $counts['subject_pair']++;
                                    $violations[] = "{$nameA} and {$nameB} for {$klassName}: one is on Day {$d} but not the other (rule: must follow).";
                                }
                                break;
                        }
                    }
                }
            }
        }

        // Build summary header
        $totalConflicts = array_sum($counts);
        if ($totalConflicts > 0) {
            $parts = [];
            if ($counts['teacher_double'] > 0) $parts[] = "{$counts['teacher_double']} teacher double-booking(s)";
            if ($counts['class_double'] > 0) $parts[] = "{$counts['class_double']} class double-booking(s)";
            if ($counts['venue_double'] > 0) $parts[] = "{$counts['venue_double']} venue double-booking(s)";
            if ($counts['assistant_double'] > 0) $parts[] = "{$counts['assistant_double']} assistant teacher double-booking(s)";
            if ($counts['unavailable'] > 0) $parts[] = "{$counts['unavailable']} teacher availability conflict(s)";
            if ($counts['locked_conflict'] > 0) $parts[] = "{$counts['locked_conflict']} locked slot conflict(s)";
            if ($counts['break_span'] > 0) $parts[] = "{$counts['break_span']} block(s) spanning breaks";
            if ($counts['double_misalignment'] > 0) $parts[] = "{$counts['double_misalignment']} misaligned double(s)";
            if ($counts['out_of_bounds'] > 0) $parts[] = "{$counts['out_of_bounds']} period(s) out of bounds";
            if ($counts['coupling'] > 0) $parts[] = "{$counts['coupling']} optional subject misalignment(s)";
            if ($counts['coupling_day_conflict'] > 0) $parts[] = "{$counts['coupling_day_conflict']} coupling day conflict(s)";
            if ($counts['core_elective_overlap'] > 0) $parts[] = "{$counts['core_elective_overlap']} core/elective overlap(s)";
            if ($counts['subject_pair'] > 0) $parts[] = "{$counts['subject_pair']} subject pair violation(s)";
            if ($counts['period_restriction'] > 0) $parts[] = "{$counts['period_restriction']} period restriction violation(s)";

            $available = $this->data->periodsPerDay * $this->data->cycleDays;
            $summary = "Found {$totalConflicts} unresolved conflict(s) after 500 generations ({$this->data->periodsPerDay} periods/day × {$this->data->cycleDays} days = {$available} slots per class): " . implode(', ', $parts) . ".";
            array_unshift($violations, $summary);
        }

        return array_values(array_unique($violations));
    }

    /**
     * Build a map of gene indices to their hard violations.
     *
     * Used for partial placement: genes NOT in the returned map are "clean"
     * and safe to persist. For double-bookings, BOTH conflicting genes are
     * marked so neither side of a conflict gets persisted.
     *
     * Called once at the end — not performance-critical.
     *
     * @return array<int, string[]> gene_index => list of violation descriptions
     */
    public function getGeneViolationMap(Chromosome $chromosome): array {
        $map = [];
        $genes = $chromosome->genes;

        [$teacherOccupancy, $klassOccupancy, $venueOccupancy,
         $assistantTeacherOccupancy, $gradeCoreOccupancy, $gradeOptionalOccupancy] = $this->buildAllOccupancyMaps($chromosome);

        foreach ($genes as $i => $gene) {
            if ($gene->dayOfCycle === 0) {
                continue;
            }

            $teacherName = $this->data->teacherNames[$gene->teacherId] ?? "Teacher #{$gene->teacherId}";
            $klassName = $this->data->klassNames[$gene->klassId] ?? "Class #{$gene->klassId}";
            $subjectName = $this->data->subjectNames[$gene->subjectId] ?? "Subject #{$gene->subjectId}";
            $day = $gene->dayOfCycle;

            for ($p = $gene->startPeriod; $p < $gene->startPeriod + $gene->duration; $p++) {
                // Period out of bounds
                if ($p > $this->data->periodsPerDay || $p < 1) {
                    $map[$i][] = "Period {$p} out of bounds for {$subjectName} ({$klassName})";
                    continue;
                }

                // Core vs coupled-elective overlap — mark BOTH genes
                if ($gene->gradeId > 0) {
                    $gKey = "{$gene->gradeId}:{$day}:{$p}";
                    if ($gene->optionalSubjectId !== null && isset($gradeCoreOccupancy[$gKey]) && $gradeCoreOccupancy[$gKey] !== $i) {
                        $otherIdx = $gradeCoreOccupancy[$gKey];
                        $reason = "Coupled elective overlaps core slot on Grade {$gene->gradeId}, Day {$day}, Period {$p}";
                        $map[$i][] = $reason;
                        $map[$otherIdx][] = $reason;
                    } elseif ($gene->klassId > 0 && isset($gradeOptionalOccupancy[$gKey]) && $gradeOptionalOccupancy[$gKey] !== $i) {
                        $otherIdx = $gradeOptionalOccupancy[$gKey];
                        $reason = "Core slot overlaps coupled elective on Grade {$gene->gradeId}, Day {$day}, Period {$p}";
                        $map[$i][] = $reason;
                        $map[$otherIdx][] = $reason;
                    }

                    if ($gene->optionalSubjectId !== null && isset($this->lockedGradeCoreOccupancy[$gKey])) {
                        $map[$i][] = "Coupled elective conflicts with locked core slot on Grade {$gene->gradeId}, Day {$day}, Period {$p}";
                    } elseif ($gene->klassId > 0 && isset($this->lockedGradeOptionalOccupancy[$gKey])) {
                        $map[$i][] = "Core slot conflicts with locked elective slot on Grade {$gene->gradeId}, Day {$day}, Period {$p}";
                    }
                }

                // Teacher double-booking — mark BOTH genes
                if ($gene->teacherId) {
                    $tKey = "{$gene->teacherId}:{$day}:{$p}";
                    if (isset($teacherOccupancy[$tKey]) && $teacherOccupancy[$tKey] !== $i) {
                        $otherIdx = $teacherOccupancy[$tKey];
                        $otherGene = $genes[$otherIdx];
                        $otherSubject = $this->data->subjectNames[$otherGene->subjectId] ?? "Subject #{$otherGene->subjectId}";
                        $otherKlass = $this->data->klassNames[$otherGene->klassId] ?? "Class #{$otherGene->klassId}";
                        $reason = "{$teacherName} double-booked Day {$day} P{$p}: {$subjectName} ({$klassName}) vs {$otherSubject} ({$otherKlass})";
                        $map[$i][] = $reason;
                        $map[$otherIdx][] = $reason;
                    }
                    if (isset($this->lockedTeacherOccupancy[$tKey])) {
                        $map[$i][] = "{$teacherName} conflicts with locked slot on Day {$day}, Period {$p}";
                    }
                }

                // Class double-booking — mark BOTH genes
                if ($gene->klassId) {
                    $cKey = "{$gene->klassId}:{$day}:{$p}";
                    if (isset($klassOccupancy[$cKey]) && $klassOccupancy[$cKey] !== $i) {
                        $otherIdx = $klassOccupancy[$cKey];
                        $otherGene = $genes[$otherIdx];
                        $otherSubject = $this->data->subjectNames[$otherGene->subjectId] ?? "Subject #{$otherGene->subjectId}";
                        $reason = "{$klassName} double-booked Day {$day} P{$p}: {$subjectName} vs {$otherSubject}";
                        $map[$i][] = $reason;
                        $map[$otherIdx][] = $reason;
                    }
                    if (isset($this->lockedKlassOccupancy[$cKey])) {
                        $map[$i][] = "{$klassName} conflicts with locked slot on Day {$day}, Period {$p}";
                    }
                }

                // Teacher unavailability
                if ($gene->teacherId && isset($this->unavailabilitySet["{$gene->teacherId}:{$day}:{$p}"])) {
                    $map[$i][] = "{$teacherName} unavailable on Day {$day}, Period {$p}";
                }

                // Venue double-booking — mark BOTH genes
                if ($gene->venueId > 0) {
                    $vKey = "{$gene->venueId}:{$day}:{$p}";
                    if (isset($venueOccupancy[$vKey]) && $venueOccupancy[$vKey] !== $i) {
                        $otherIdx = $venueOccupancy[$vKey];
                        $venueName = $this->data->venueNames[$gene->venueId] ?? "Venue #{$gene->venueId}";
                        $reason = "{$venueName} double-booked Day {$day} P{$p}";
                        $map[$i][] = $reason;
                        $map[$otherIdx][] = $reason;
                    }
                    if (isset($this->lockedVenueOccupancy[$vKey])) {
                        $venueName = $this->data->venueNames[$gene->venueId] ?? "Venue #{$gene->venueId}";
                        $map[$i][] = "{$venueName} conflicts with locked slot on Day {$day}, Period {$p}";
                    }
                }

                // Assistant teacher double-booking — mark BOTH genes
                if ($gene->assistantTeacherId > 0) {
                    $aKey = "{$gene->assistantTeacherId}:{$day}:{$p}";
                    if (isset($assistantTeacherOccupancy[$aKey]) && $assistantTeacherOccupancy[$aKey] !== $i) {
                        $otherIdx = $assistantTeacherOccupancy[$aKey];
                        $assistantName = $this->data->teacherNames[$gene->assistantTeacherId] ?? "Teacher #{$gene->assistantTeacherId}";
                        $reason = "Assistant {$assistantName} double-booked Day {$day} P{$p}";
                        $map[$i][] = $reason;
                        $map[$otherIdx][] = $reason;
                    }
                    if (isset($this->lockedAssistantTeacherOccupancy[$aKey])) {
                        $assistantName = $this->data->teacherNames[$gene->assistantTeacherId] ?? "Teacher #{$gene->assistantTeacherId}";
                        $map[$i][] = "Assistant {$assistantName} conflicts with locked slot on Day {$day}, Period {$p}";
                    }
                }
            }

            // Block spans break
            if ($gene->duration > 1) {
                for ($p = $gene->startPeriod; $p < $gene->startPeriod + $gene->duration - 1; $p++) {
                    if (isset($this->breakSet[$p])) {
                        $map[$i][] = "{$subjectName} ({$klassName}) block spans break after Period {$p} on Day {$day}";
                    }
                }
            }

            if (BlockPlacementRules::isMisalignedDoubleStart($gene->startPeriod, $gene->duration, $this->validDoubleStartSet)) {
                $validStartsStr = implode(', ', array_keys($this->validDoubleStartSet));
                $map[$i][] = "{$subjectName} ({$klassName}) double starts at Period {$gene->startPeriod}, but valid starts are {$validStartsStr}";
            }
        }

        // Coupling group misalignment — mark all misaligned genes
        $couplingGroups = [];
        foreach ($genes as $idx => $gene) {
            if ($gene->couplingKey !== null && $gene->dayOfCycle > 0) {
                $couplingGroups[$gene->couplingKey][$idx] = $gene;
            }
        }
        foreach ($couplingGroups as $key => $indexed) {
            $positions = [];
            foreach ($indexed as $idx => $g) {
                $positions["{$g->dayOfCycle}:{$g->startPeriod}"][] = $idx;
            }
            if (count($positions) <= 1) {
                continue;
            }
            // Find majority position to only mark the minority genes
            $positionCounts = array_map('count', $positions);
            arsort($positionCounts);
            $majorityPos = array_key_first($positionCounts);
            foreach ($positions as $posKey => $geneIndices) {
                if ($posKey !== $majorityPos) {
                    foreach ($geneIndices as $idx) {
                        $map[$idx][] = "Coupling group '{$key}' misaligned at Day/Period {$posKey}";
                    }
                }
            }
        }

        // Coupling day conflict — mark genes from labels sharing a day within the same grade
        $couplingKeyDayGVM = [];
        foreach ($couplingGroups as $key => $indexed) {
            if (empty($indexed)) {
                continue;
            }
            $dayCounts = [];
            foreach ($indexed as $g) {
                $dayCounts[$g->dayOfCycle] = ($dayCounts[$g->dayOfCycle] ?? 0) + 1;
            }
            arsort($dayCounts);
            $couplingKeyDayGVM[$key] = (int) array_key_first($dayCounts);
        }
        $gradeLabelDaysGVM = [];
        $gradeLabelKeysGVM = [];
        foreach ($couplingKeyDayGVM as $key => $day) {
            if (preg_match('/^cg_(\d+)_(.+)_[sdt]\d+$/', $key, $m)) {
                $gid = (int) $m[1];
                $lbl = $m[2];
                $gradeLabelDaysGVM[$gid][$lbl][$day] = true;
                $gradeLabelKeysGVM[$gid][$lbl][] = $key;
            }
        }
        foreach ($gradeLabelDaysGVM as $gradeId => $labels) {
            $labelNames = array_keys($labels);
            $labelCount = count($labelNames);
            for ($li = 0; $li < $labelCount; $li++) {
                for ($lj = $li + 1; $lj < $labelCount; $lj++) {
                    $sharedDays = array_keys(array_intersect_key($labels[$labelNames[$li]], $labels[$labelNames[$lj]]));
                    if (empty($sharedDays)) {
                        continue;
                    }
                    $affectedKeys = array_merge(
                        $gradeLabelKeysGVM[$gradeId][$labelNames[$li]] ?? [],
                        $gradeLabelKeysGVM[$gradeId][$labelNames[$lj]] ?? []
                    );
                    foreach ($sharedDays as $sharedDay) {
                        foreach ($affectedKeys as $cgKey) {
                            if (($couplingKeyDayGVM[$cgKey] ?? 0) !== $sharedDay) {
                                continue;
                            }
                            foreach (array_keys($couplingGroups[$cgKey] ?? []) as $idx) {
                                $map[$idx][] = "Coupling groups '{$labelNames[$li]}' and '{$labelNames[$lj]}' (grade {$gradeId}) share Day {$sharedDay}";
                            }
                        }
                    }
                }
            }
        }

        return $map;
    }

    /**
     * Build all occupancy hash maps from chromosome genes in a single pass.
     *
     * @return array [$teacherOccupancy, $klassOccupancy, $venueOccupancy, $assistantTeacherOccupancy, $gradeCoreOccupancy, $gradeOptionalOccupancy]
     */
    private function buildAllOccupancyMaps(Chromosome $chromosome): array {
        $teacherOccupancy = [];
        $klassOccupancy = [];
        $venueOccupancy = [];
        $assistantTeacherOccupancy = [];
        $gradeCoreOccupancy = [];
        $gradeOptionalOccupancy = [];

        foreach ($chromosome->genes as $i => $gene) {
            if ($gene->dayOfCycle === 0) {
                continue;
            }

            $day = $gene->dayOfCycle;
            for ($p = $gene->startPeriod; $p < $gene->startPeriod + $gene->duration; $p++) {
                if ($gene->teacherId) {
                    $tKey = "{$gene->teacherId}:{$day}:{$p}";
                    if (!isset($teacherOccupancy[$tKey])) {
                        $teacherOccupancy[$tKey] = $i;
                    }
                }

                if ($gene->klassId) {
                    $cKey = "{$gene->klassId}:{$day}:{$p}";
                    if (!isset($klassOccupancy[$cKey])) {
                        $klassOccupancy[$cKey] = $i;
                    }
                }

                if ($gene->venueId > 0) {
                    $vKey = "{$gene->venueId}:{$day}:{$p}";
                    if (!isset($venueOccupancy[$vKey])) {
                        $venueOccupancy[$vKey] = $i;
                    }
                }

                if ($gene->assistantTeacherId > 0) {
                    $aKey = "{$gene->assistantTeacherId}:{$day}:{$p}";
                    if (!isset($assistantTeacherOccupancy[$aKey])) {
                        $assistantTeacherOccupancy[$aKey] = $i;
                    }
                }

                if ($gene->gradeId > 0) {
                    $gKey = "{$gene->gradeId}:{$day}:{$p}";
                    if ($gene->optionalSubjectId !== null) {
                        if (!isset($gradeOptionalOccupancy[$gKey])) {
                            $gradeOptionalOccupancy[$gKey] = $i;
                        }
                    } elseif ($gene->klassId > 0) {
                        if (!isset($gradeCoreOccupancy[$gKey])) {
                            $gradeCoreOccupancy[$gKey] = $i;
                        }
                    }
                }
            }
        }

        return [$teacherOccupancy, $klassOccupancy, $venueOccupancy,
                $assistantTeacherOccupancy, $gradeCoreOccupancy, $gradeOptionalOccupancy];
    }

    /**
     * Compact gene descriptor for reports.
     */
    private function describeGene(Gene $gene): string {
        $subjectName = $this->data->subjectNames[$gene->subjectId] ?? "Subject #{$gene->subjectId}";
        if ($gene->optionalSubjectId !== null) {
            return "{$subjectName} (elective)";
        }

        $klassName = $this->data->klassNames[$gene->klassId] ?? "Class #{$gene->klassId}";
        return "{$subjectName} ({$klassName})";
    }

    /**
     * Find longest consecutive run in a sorted array of period numbers.
     */
    private function findLongestConsecutiveRun(array $sortedPeriods): int {
        if (empty($sortedPeriods)) {
            return 0;
        }

        $maxRun = 1;
        $currentRun = 1;
        $sortedPeriods = array_values($sortedPeriods);

        for ($i = 1, $count = count($sortedPeriods); $i < $count; $i++) {
            if ($sortedPeriods[$i] === $sortedPeriods[$i - 1] + 1) {
                $currentRun++;
                if ($currentRun > $maxRun) {
                    $maxRun = $currentRun;
                }
            } else {
                $currentRun = 1;
            }
        }

        return $maxRun;
    }

    /**
     * Calculate standard deviation of an array of values.
     */
    private function calculateStdDev(array $values): float {
        $count = count($values);
        if ($count < 2) {
            return 0.0;
        }

        $mean = array_sum($values) / $count;
        $sumSquaredDiffs = 0.0;
        foreach ($values as $v) {
            $sumSquaredDiffs += ($v - $mean) ** 2;
        }

        return sqrt($sumSquaredDiffs / $count);
    }
}

<?php

namespace App\Services\Timetable\Generation;

/**
 * A complete timetable solution in the genetic algorithm.
 *
 * Holds an array of Gene objects representing all teaching blocks that need scheduling,
 * along with a fitness score (0.0 worst to 1.0 perfect) and hard violation count.
 */
class Chromosome {
    /**
     * Number of hard constraint violations (set by FitnessEvaluator).
     */
    public int $hardViolationCount = 0;

    /**
     * @param Gene[] $genes   All scheduling units in this solution
     * @param float  $fitness Fitness score from 0.0 (worst) to 1.0 (perfect)
     */
    public function __construct(
        public array $genes,
        public float $fitness = 0.0,
    ) {}

    /**
     * Create a chromosome by expanding block allocations into individual genes.
     *
     * Each allocation produces: singles genes (duration=1), doubles genes (duration=2),
     * triples genes (duration=3). All start unassigned (dayOfCycle=0, startPeriod=0).
     *
     * @param array $allocations Array of assoc arrays with keys:
     *   klass_subject_id, teacher_id, klass_id, subject_id, singles, doubles, triples
     * @param array $couplingGroups Array of coupling group arrays with keys:
     *   grade_id, label, optional_subject_ids[], singles, doubles, triples
     * @param array $optionalSubjectMap Keyed by optional_subject_id => [teacher_id, subject_id]
     * @return self
     */
    public static function fromAllocations(array $allocations, array $couplingGroups = [], array $optionalSubjectMap = []): self {
        $genes = [];

        // Standard KlassSubject allocations
        foreach ($allocations as $alloc) {
            $singles = (int) ($alloc['singles'] ?? 0);
            $doubles = (int) ($alloc['doubles'] ?? 0);
            $triples = (int) ($alloc['triples'] ?? 0);

            $venueId = (int) ($alloc['venue_id'] ?? 0);
            $assistantTeacherId = (int) ($alloc['assistant_user_id'] ?? 0);

            for ($i = 0; $i < $singles; $i++) {
                $genes[] = new Gene(
                    klassSubjectId: (int) $alloc['klass_subject_id'],
                    teacherId: (int) $alloc['teacher_id'],
                    klassId: (int) $alloc['klass_id'],
                    subjectId: (int) $alloc['subject_id'],
                    duration: 1,
                    gradeId: (int) ($alloc['grade_id'] ?? 0),
                    venueId: $venueId,
                    assistantTeacherId: $assistantTeacherId,
                );
            }

            for ($i = 0; $i < $doubles; $i++) {
                $genes[] = new Gene(
                    klassSubjectId: (int) $alloc['klass_subject_id'],
                    teacherId: (int) $alloc['teacher_id'],
                    klassId: (int) $alloc['klass_id'],
                    subjectId: (int) $alloc['subject_id'],
                    duration: 2,
                    gradeId: (int) ($alloc['grade_id'] ?? 0),
                    venueId: $venueId,
                    assistantTeacherId: $assistantTeacherId,
                );
            }

            for ($i = 0; $i < $triples; $i++) {
                $genes[] = new Gene(
                    klassSubjectId: (int) $alloc['klass_subject_id'],
                    teacherId: (int) $alloc['teacher_id'],
                    klassId: (int) $alloc['klass_id'],
                    subjectId: (int) $alloc['subject_id'],
                    duration: 3,
                    gradeId: (int) ($alloc['grade_id'] ?? 0),
                    venueId: $venueId,
                    assistantTeacherId: $assistantTeacherId,
                );
            }
        }

        // Coupling group genes (optional subjects)
        foreach ($couplingGroups as $group) {
            $gradeId = (int) ($group['grade_id'] ?? 0);
            $label = $group['label'] ?? 'unknown';
            $optSubjectIds = $group['optional_subject_ids'] ?? [];
            $singles = (int) ($group['singles'] ?? 0);
            $doubles = (int) ($group['doubles'] ?? 0);
            $triples = (int) ($group['triples'] ?? 0);

            // Singles
            for ($i = 0; $i < $singles; $i++) {
                $couplingKey = "cg_{$gradeId}_{$label}_s{$i}";
                foreach ($optSubjectIds as $osId) {
                    $osId = (int) $osId;
                    $info = $optionalSubjectMap[$osId] ?? ['teacher_id' => 0, 'subject_id' => 0];
                    $genes[] = new Gene(
                        klassSubjectId: 0,
                        teacherId: (int) $info['teacher_id'],
                        klassId: 0,
                        subjectId: (int) $info['subject_id'],
                        duration: 1,
                        gradeId: (int) ($info['grade_id'] ?? $gradeId),
                        couplingKey: $couplingKey,
                        optionalSubjectId: $osId,
                        venueId: (int) ($info['venue_id'] ?? 0),
                    );
                }
            }

            // Doubles
            for ($i = 0; $i < $doubles; $i++) {
                $couplingKey = "cg_{$gradeId}_{$label}_d{$i}";
                foreach ($optSubjectIds as $osId) {
                    $osId = (int) $osId;
                    $info = $optionalSubjectMap[$osId] ?? ['teacher_id' => 0, 'subject_id' => 0];
                    $genes[] = new Gene(
                        klassSubjectId: 0,
                        teacherId: (int) $info['teacher_id'],
                        klassId: 0,
                        subjectId: (int) $info['subject_id'],
                        duration: 2,
                        gradeId: (int) ($info['grade_id'] ?? $gradeId),
                        couplingKey: $couplingKey,
                        optionalSubjectId: $osId,
                        venueId: (int) ($info['venue_id'] ?? 0),
                    );
                }
            }

            // Triples
            for ($i = 0; $i < $triples; $i++) {
                $couplingKey = "cg_{$gradeId}_{$label}_t{$i}";
                foreach ($optSubjectIds as $osId) {
                    $osId = (int) $osId;
                    $info = $optionalSubjectMap[$osId] ?? ['teacher_id' => 0, 'subject_id' => 0];
                    $genes[] = new Gene(
                        klassSubjectId: 0,
                        teacherId: (int) $info['teacher_id'],
                        klassId: 0,
                        subjectId: (int) $info['subject_id'],
                        duration: 3,
                        gradeId: (int) ($info['grade_id'] ?? $gradeId),
                        couplingKey: $couplingKey,
                        optionalSubjectId: $osId,
                        venueId: (int) ($info['venue_id'] ?? 0),
                    );
                }
            }
        }

        return new self($genes);
    }

    /**
     * Deep clone: each Gene must be cloned individually.
     */
    public function __clone(): void {
        $this->genes = array_map(fn(Gene $gene) => clone $gene, $this->genes);
    }

    /**
     * Get total number of genes.
     */
    public function getTotalGenes(): int {
        return count($this->genes);
    }

    /**
     * Get count of unassigned genes (dayOfCycle === 0).
     */
    public function getUnassignedCount(): int {
        $count = 0;
        foreach ($this->genes as $gene) {
            if ($gene->dayOfCycle === 0) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Check if chromosome has hard constraint violations.
     */
    public function hasHardViolations(): bool {
        return $this->hardViolationCount > 0;
    }
}

<?php

namespace App\Services\Timetable\Generation;

/**
 * Container for the output of a genetic algorithm run.
 *
 * Holds the best chromosome found, generation statistics, violation report,
 * and per-gene violation map for partial placement support.
 */
class GenerationResult {
    public function __construct(
        /** The best chromosome (timetable solution) found by the GA. */
        public readonly Chromosome $chromosome,

        /** Number of generations completed. */
        public readonly int $generations,

        /** Fitness score of the best chromosome (0.0 to 1.0). */
        public readonly float $fitness,

        /** Total number of assigned gene slots. */
        public readonly int $totalSlots,

        /** Number of hard constraint violations remaining. */
        public readonly int $hardViolationCount,

        /** Human-readable violation report strings. */
        public readonly array $violationReport,

        /**
         * Per-gene violation map: gene_index => [violation descriptions].
         * Genes NOT in this map are clean and safe to persist.
         * @var array<int, string[]>
         */
        public readonly array $geneViolationMap = [],

        /** Number of genes without violations (safe to place). */
        public readonly int $placedCount = 0,

        /** Number of genes with violations (skipped). */
        public readonly int $skippedCount = 0,
    ) {}

    /**
     * Check if the solution has unresolved hard violations.
     */
    public function hasHardViolations(): bool {
        return $this->hardViolationCount > 0;
    }

    /**
     * Get the violation report.
     *
     * @return string[]
     */
    public function getViolationReport(): array {
        return $this->violationReport;
    }
}

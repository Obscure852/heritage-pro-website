<?php

namespace App\Jobs\Timetable;

use App\Models\Timetable\Timetable;
use App\Services\Timetable\GenerationDataLoader;
use App\Services\Timetable\TimetableGeneratorService;
use App\Services\Timetable\TimetableIntegrityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Queued job that runs the genetic algorithm to generate a timetable.
 *
 * Writes progress updates to cache for AJAX polling.
 * Implements ShouldBeUnique to prevent duplicate concurrent runs.
 */
class GenerateTimetableJob implements ShouldQueue, ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout;
    public int $tries = 1; // No retry -- GA is non-deterministic
    public int $uniqueFor;

    private ?array $lastProgressPayload = null;
    private float $lastProgressWriteAt = 0.0;

    public function __construct(
        public Timetable $timetable,
        public int $userId,
    ) {
        $this->timeout = (int) config('queue.timetable_generation.timeout', 21600);
        $this->uniqueFor = (int) config('queue.timetable_generation.unique_for', 22200);

        $this->onConnection((string) config('queue.timetable_generation.connection', 'database_timetable'));
        $this->onQueue((string) config('queue.timetable_generation.queue', 'timetable-generation'));
    }

    /**
     * Unique ID to prevent duplicate concurrent runs for same timetable.
     */
    public function uniqueId(): string {
        return 'timetable_generation_' . $this->timetable->id;
    }

    /**
     * Execute the job.
     */
    public function handle(
        TimetableGeneratorService $generator,
        GenerationDataLoader $loader,
        TimetableIntegrityService $integrityService
    ): void {
        $this->configureRuntimeLimits();
        $this->updateProgress('loading', 0, 'Loading timetable data...');

        try {
            $this->updateProgress('loading', 1, 'Checking existing timetable integrity...');
            $repair = $integrityService->repairNonLocked($this->timetable->id);
            $lockedBlockers = $repair['unresolved_locked'] ?? [];
            $lockedBlockerWarnings = [];
            if (!empty($lockedBlockers)) {
                $lockedBlockerWarnings = $this->buildLockedBlockerMessages($lockedBlockers);
                $this->updateProgress(
                    'loading',
                    2,
                    'Locked integrity issues detected. Continuing with best-effort generation around locked slots...',
                    $lockedBlockerWarnings
                );
            }

            $data = $loader->load($this->timetable);

            // Pre-flight validation
            $issues = $generator->validatePreConditions($data);
            if (!empty($issues)) {
                $this->updateProgress('failed', 0, 'Pre-flight validation failed', $issues);
                return;
            }

            $this->updateProgress('generating', 5, 'Starting genetic algorithm...');

            $cancelKey = $this->cancelCacheKey();
            // Clear any stale cancel flag before starting
            Cache::forget($cancelKey);

            $wasCancelled = false;

            $result = $generator->generate(
                $data,
                function (int $gen, int $maxGen, float $bestFitness) {
                    $pct = 5 + (int) (($gen / $maxGen) * 85); // 5-90% range for GA
                    $this->updateProgress(
                        'generating',
                        $pct,
                        "Generation {$gen}/{$maxGen} — Fitness: " . number_format($bestFitness, 4)
                    );
                },
                function () use ($cancelKey, &$wasCancelled): bool {
                    if (Cache::get($cancelKey, false)) {
                        $wasCancelled = true;
                        return true;
                    }
                    return false;
                }
            );

            // Clean up cancel flag
            Cache::forget($cancelKey);

            if ($wasCancelled) {
                $this->updateProgress('saving', 92, 'Stopping — saving best solution found so far...');

                $generator->persistSolution($this->timetable, $result, $this->userId);

                $conflictNote = $result->hasHardViolations()
                    ? " ({$result->hardViolationCount} conflict(s) remain)"
                    : '';
                $this->updateProgress(
                    'cancelled',
                    100,
                    "Stopped after {$result->generations} generation(s). "
                        . "All {$result->totalSlots} slot(s) saved{$conflictNote}. "
                        . "Fitness: " . number_format($result->fitness, 4)
                );
                return;
            }

            if ($result->hasHardViolations()) {
                $this->updateProgress('saving', 92, 'Saving timetable — conflicts will be flagged for review...');
                $generator->persistSolution($this->timetable, $result, $this->userId);

                $errors = array_values(array_unique(array_merge(
                    $lockedBlockerWarnings,
                    $result->getViolationReport()
                )));
                $this->updateProgress(
                    'completed_with_conflicts',
                    100,
                    "All {$result->totalSlots} slots placed. {$result->hardViolationCount} conflict(s) detected — review the grid to lock good slots and regenerate.",
                    $errors
                );
                return;
            }

            $this->updateProgress('saving', 95, 'Saving generated timetable...');

            $generator->persistSolution($this->timetable, $result, $this->userId);

            $this->updateProgress(
                'completed',
                100,
                "Complete! {$result->totalSlots} slots created, fitness: " . number_format($result->fitness, 4)
            );
        } catch (\Exception $e) {
            Cache::forget($this->cancelCacheKey());
            Log::error('Timetable generation failed', [
                'timetable_id' => $this->timetable->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->updateProgress('failed', 0, 'Generation failed', [$e->getMessage()]);
        }
    }

    /**
     * Write progress data to cache for AJAX polling.
     */
    private function updateProgress(string $status, int $percent, ?string $message = null, ?array $errors = null): void {
        $payload = [
            'status' => $status,
            'percent' => $percent,
            'message' => $message,
            'errors' => $errors,
            'updated_at' => now()->toISOString(),
        ];

        if ($status === 'generating' && $this->shouldSkipProgressWrite($payload)) {
            return;
        }

        Cache::forever($this->statusCacheKey(), $payload);
        $this->lastProgressPayload = $payload;
        $this->lastProgressWriteAt = microtime(true);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $e): void {
        Cache::forget($this->cancelCacheKey());
        Log::error('Timetable generation job failed', [
            'timetable_id' => $this->timetable->id,
            'error' => $e->getMessage(),
        ]);
        $this->updateProgress('failed', 0, 'Generation failed unexpectedly', [$e->getMessage()]);
    }

    /**
     * Raise PHP execution ceiling for long-running timetable generation.
     *
     * Queue worker timeout and queue retry_after are configured separately;
     * this prevents PHP's max_execution_time (often 300s) from killing the job first.
     */
    private function configureRuntimeLimits(): void {
        @ini_set('memory_limit', '1024M'); // Prevent out-of-memory errors on 1K+ gene profiles
        $timeout = max(0, (int) $this->timeout);
        // 0 means unlimited in PHP; we use job timeout when set.
        if ($timeout > 0) {
            @ini_set('max_execution_time', (string) $timeout);
            @set_time_limit($timeout);
            return;
        }

        @ini_set('max_execution_time', '0');
        @set_time_limit(0);
    }

    /**
     * @param array<int, array<string, mixed>> $lockedBlockers
     * @return string[]
     */
    private function buildLockedBlockerMessages(array $lockedBlockers): array {
        $messages = [];

        foreach ($lockedBlockers as $issue) {
            $message = (string) ($issue['message'] ?? '');
            $type = (string) ($issue['type'] ?? 'integrity_issue');
            $slotIds = array_map('intval', (array) ($issue['locked_slot_ids'] ?? []));

            if ($message !== '') {
                if (!empty($slotIds)) {
                    $messages[] = "{$message} Locked slot IDs: " . implode(', ', $slotIds) . ".";
                } else {
                    $messages[] = $message;
                }
                continue;
            }

            if (!empty($slotIds)) {
                $messages[] = "Locked {$type} issue on slot IDs: " . implode(', ', $slotIds) . ".";
            } else {
                $messages[] = "Locked {$type} issue detected.";
            }
        }

        return $messages;
    }

    private function shouldSkipProgressWrite(array $payload): bool {
        if ($this->lastProgressPayload === null) {
            return false;
        }

        $statusChanged = ($this->lastProgressPayload['status'] ?? null) !== ($payload['status'] ?? null);
        $percentChanged = ($this->lastProgressPayload['percent'] ?? null) !== ($payload['percent'] ?? null);
        $errorsChanged = ($this->lastProgressPayload['errors'] ?? null) !== ($payload['errors'] ?? null);

        if ($statusChanged || $percentChanged || $errorsChanged) {
            return false;
        }

        return (microtime(true) - $this->lastProgressWriteAt) < 2.0;
    }

    private function statusCacheKey(): string {
        return "generation_status_{$this->timetable->id}";
    }

    private function cancelCacheKey(): string {
        return "generation_cancel_{$this->timetable->id}";
    }
}

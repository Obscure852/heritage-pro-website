<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\Timetable\GenerateTimetableRequest;
use App\Jobs\Timetable\GenerateTimetableJob;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableBlockAllocation;
use App\Models\Timetable\TimetableConstraint;
use App\Models\Timetable\TimetableSetting;
use App\Models\Timetable\TimetableSlot;
use App\Services\Timetable\TimetableGeneratorService;
use Illuminate\Bus\UniqueLock;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * HTTP layer for automated timetable generation.
 *
 * Provides endpoints to trigger generation, poll progress, and display the generation page.
 */
class GenerationController extends Controller {
    public function __construct() {
        $this->middleware(['auth', 'can:manage-timetable']);
    }

    /**
     * Display the generation dashboard page.
     */
    public function index(Timetable $timetable): View {
        $status = Cache::get($this->statusCacheKey($timetable->id), [
            'status' => 'idle',
            'percent' => 0,
            'message' => null,
            'errors' => null,
        ]);

        $hasAllocations = TimetableBlockAllocation::where('timetable_id', $timetable->id)->exists();
        $hasConstraints = TimetableConstraint::where('timetable_id', $timetable->id)->active()->exists();
        $existingSlotCount = TimetableSlot::where('timetable_id', $timetable->id)
            ->where('is_locked', false)
            ->count();

        $lockedSlotCount = TimetableSlot::where('timetable_id', $timetable->id)
            ->where('is_locked', true)
            ->count();

        return view('timetable.generation.index', compact(
            'timetable',
            'status',
            'hasAllocations',
            'hasConstraints',
            'existingSlotCount',
            'lockedSlotCount'
        ));
    }

    /**
     * Display the GA advanced settings page.
     */
    public function settings(): View {
        $timetable = Timetable::draft()->first();

        $geneCount = 0;
        if ($timetable) {
            $geneCount = (int) (TimetableBlockAllocation::where('timetable_id', $timetable->id)
                ->selectRaw('SUM(singles + doubles + triples) as total')
                ->value('total') ?? 0);
        }

        $savedParameters = TimetableSetting::get('ga_parameters', []);
        $recommendedProfile = TimetableGeneratorService::recommendProfile($geneCount);
        $gaProfiles = TimetableGeneratorService::GA_PROFILES;
        $defaultParameters = TimetableGeneratorService::DEFAULT_GA_PARAMETERS;

        return view('timetable.generation.settings', compact(
            'geneCount',
            'savedParameters',
            'recommendedProfile',
            'gaProfiles',
            'defaultParameters'
        ));
    }

    /**
     * Display the scheduling engine documentation page (system admins only).
     */
    public function documentation(): View {
        return view('timetable.generation.documentation');
    }

    /**
     * Trigger timetable generation (dispatches to queue).
     */
    public function generate(GenerateTimetableRequest $request): JsonResponse {
        try {
            $timetable = Timetable::findOrFail($request->validated()['timetable_id']);
            $userId = (int) auth()->id();
            $statusKey = "generation_status_{$timetable->id}";

            // Use explicit lock handling so we can recover stale unique locks.
            $queued = $this->dispatchGenerationJob($timetable, $userId);
            if (!$queued) {
                if ($this->hasQueuedGenerationJob($timetable->id)) {
                    Cache::forever($statusKey, [
                        'status' => 'queued',
                        'percent' => 0,
                        'message' => 'Generation already queued/running...',
                        'errors' => null,
                        'updated_at' => now()->toISOString(),
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Generation already in progress. Monitoring progress...',
                    ]);
                }

                // No matching queued job exists; recover stale unique lock and retry once.
                $this->releaseGenerationLock($timetable, $userId);
                $queued = $this->dispatchGenerationJob($timetable, $userId);

                if (!$queued) {
                    Cache::forever($statusKey, [
                        'status' => 'failed',
                        'percent' => 0,
                        'message' => 'Could not queue generation because a lock is still active.',
                        'errors' => ['A generation lock is active. Please wait a moment and try again.'],
                        'updated_at' => now()->toISOString(),
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Could not queue generation. Please wait a moment and try again.',
                    ], 409);
                }
            }

            Cache::forever($statusKey, [
                'status' => 'queued',
                'percent' => 0,
                'message' => 'Queued for generation...',
                'errors' => null,
                'updated_at' => now()->toISOString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Generation started. Monitoring progress...',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to queue timetable generation', [
                'timetable_id' => $request->validated()['timetable_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error starting generation: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Dispatch the generation job after acquiring the unique lock.
     */
    private function dispatchGenerationJob(Timetable $timetable, int $userId): bool {
        $job = new GenerateTimetableJob($timetable, $userId);
        $uniqueLock = app(UniqueLock::class);

        if (!$uniqueLock->acquire($job)) {
            return false;
        }

        try {
            app(Dispatcher::class)->dispatch($job);
            return true;
        } catch (\Throwable $e) {
            // Lock was acquired here, so release it on dispatch failure.
            $uniqueLock->release($job);
            throw $e;
        }
    }

    /**
     * Force-release the unique lock for a timetable generation job.
     */
    private function releaseGenerationLock(Timetable $timetable, int $userId): void {
        $job = new GenerateTimetableJob($timetable, $userId);
        app(UniqueLock::class)->release($job);
    }

    /**
     * Check if a queued/running generation job already exists for a timetable.
     */
    private function hasQueuedGenerationJob(int $timetableId): bool {
        $queueName = (string) config('queue.timetable_generation.queue', 'timetable-generation');

        $payloads = DB::table('jobs')
            ->where('queue', $queueName)
            ->where('payload', 'like', '%App\\\\Jobs\\\\Timetable\\\\GenerateTimetableJob%')
            ->pluck('payload');

        foreach ($payloads as $payload) {
            if ($this->extractTimetableIdFromPayload((string) $payload) === $timetableId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse timetable ID from serialized queue payload.
     */
    private function extractTimetableIdFromPayload(string $payload): ?int {
        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            return null;
        }

        if (($decoded['displayName'] ?? null) !== GenerateTimetableJob::class) {
            return null;
        }

        $command = (string) ($decoded['data']['command'] ?? '');
        if ($command === '') {
            return null;
        }

        if (preg_match('/App\\\\Models\\\\Timetable\\\\Timetable";s:2:"id";i:(\d+);/', $command, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * Save custom GA parameters.
     */
    public function saveParameters(Request $request): JsonResponse {
        $validated = $request->validate([
            'population_size' => 'required|integer|min:10|max:500',
            'max_generations' => 'required|integer|min:50|max:5000',
            'mutation_rate' => 'required|numeric|min:0.01|max:0.5',
            'crossover_rate' => 'required|numeric|min:0.1|max:1.0',
            'tournament_size' => 'required|integer|min:2|max:20',
            'elite_count' => 'required|integer|min:1|max:20',
            'stagnation_limit' => 'required|integer|min:5|max:500',
            'repair_moves' => 'required|integer|min:1|max:30',
        ]);

        // Merge with defaults so all 11 keys are always persisted (the UI only
        // exposes 8 — the 3 internal params keep their default values).
        $params = array_merge(TimetableGeneratorService::DEFAULT_GA_PARAMETERS, $validated);
        TimetableSetting::set('ga_parameters', $params, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'GA parameters saved.',
        ]);
    }

    /**
     * Apply a predefined GA profile.
     */
    public function applyProfile(Request $request): JsonResponse {
        $validated = $request->validate([
            'profile' => 'required|string|in:' . implode(',', array_keys(TimetableGeneratorService::GA_PROFILES)),
        ]);

        $profile = TimetableGeneratorService::GA_PROFILES[$validated['profile']];
        $params = array_merge(TimetableGeneratorService::DEFAULT_GA_PARAMETERS, $profile['params']);

        TimetableSetting::set('ga_parameters', $params, auth()->id());

        return response()->json([
            'success' => true,
            'message' => "Applied '{$profile['label']}' profile.",
            'parameters' => $params,
        ]);
    }

    /**
     * Request cancellation of a running generation.
     */
    public function cancel(Timetable $timetable): JsonResponse {
        $current = Cache::get($this->statusCacheKey($timetable->id));
        $activeStatuses = ['queued', 'loading', 'generating', 'saving'];

        if (!in_array($current['status'] ?? '', $activeStatuses, true)) {
            return response()->json([
                'success' => false,
                'message' => 'No active generation to cancel.',
            ], 409);
        }

        Cache::forever($this->cancelCacheKey($timetable->id), true);

        return response()->json([
            'success' => true,
            'message' => 'Cancellation requested. The algorithm will stop after the current generation.',
        ]);
    }

    /**
     * Return current generation progress status as JSON.
     */
    public function status(Timetable $timetable): JsonResponse {
        $status = Cache::get($this->statusCacheKey($timetable->id), [
            'status' => 'idle',
            'percent' => 0,
            'message' => null,
            'errors' => null,
        ]);

        return response()->json($status);
    }

    private function statusCacheKey(int $timetableId): string {
        return "generation_status_{$timetableId}";
    }

    private function cancelCacheKey(int $timetableId): string {
        return "generation_cancel_{$timetableId}";
    }
}

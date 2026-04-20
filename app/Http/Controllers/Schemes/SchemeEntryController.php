<?php

namespace App\Http\Controllers\Schemes;

use App\Helpers\SyllabusStructureHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Schemes\UpdateSchemeEntryRequest;
use App\Models\Schemes\SchemeOfWork;
use App\Models\Schemes\SchemeOfWorkEntry;
use App\Models\Schemes\Syllabus;
use App\Models\Schemes\SyllabusObjective;
use App\Models\Schemes\SyllabusTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SchemeEntryController extends Controller {
    public function update(UpdateSchemeEntryRequest $request, SchemeOfWork $scheme, SchemeOfWorkEntry $entry): JsonResponse {
        $this->authorize('update', $scheme);

        abort_if($entry->scheme_of_work_id !== $scheme->id, 404);

        // Block edits on entries distributed from a standard scheme
        if ($entry->standard_scheme_entry_id) {
            return response()->json([
                'message' => 'This entry is managed by the standard scheme and cannot be edited individually.',
            ], 403);
        }

        $validated = $request->validated();
        $objectiveIds = null;

        if (array_key_exists('objective_ids', $validated)) {
            $objectiveIds = collect($validated['objective_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();
            unset($validated['objective_ids']);
        }

        $result = DB::transaction(function () use ($scheme, $entry, $validated, $objectiveIds): array {
            $lockedScheme = SchemeOfWork::query()->lockForUpdate()->findOrFail($scheme->id);
            $lockedEntry = SchemeOfWorkEntry::query()
                ->where('scheme_of_work_id', $lockedScheme->id)
                ->lockForUpdate()
                ->findOrFail($entry->id);

            if (!$this->schemeAllowsEntryEditing($lockedScheme)) {
                return ['forbidden' => 'This scheme can no longer be edited in its current status.'];
            }

            if ($this->entryLockedForEditing($lockedScheme, $lockedEntry)) {
                return ['forbidden' => 'Completed entries cannot be edited on an approved scheme.'];
            }

            $payload = $validated;
            if (array_key_exists('syllabus_topic_id', $payload)) {
                $payload['syllabus_topic_id'] = filled($payload['syllabus_topic_id'])
                    ? (int) $payload['syllabus_topic_id']
                    : null;
            }

            $selectedTopicId = array_key_exists('syllabus_topic_id', $payload)
                ? ($payload['syllabus_topic_id'] ?: null)
                : ($lockedEntry->syllabus_topic_id ? (int) $lockedEntry->syllabus_topic_id : null);

            $this->assertObjectiveSelectionAllowed($lockedScheme, $selectedTopicId, $objectiveIds);

            $lockedEntry->update($payload);

            if (is_array($objectiveIds)) {
                $lockedEntry->objectives()->sync($objectiveIds);
            }

            return [
                'entry' => $lockedEntry->fresh(['syllabusTopic', 'objectives.topic']),
            ];
        });

        if (isset($result['forbidden'])) {
            return response()->json(['message' => $result['forbidden']], 403);
        }

        return response()->json([
            'success' => true,
            'entry' => $result['entry'],
        ]);
    }

    public function syncObjectives(Request $request, SchemeOfWork $scheme, SchemeOfWorkEntry $entry): JsonResponse {
        $this->authorize('update', $scheme);

        abort_if($entry->scheme_of_work_id !== $scheme->id, 404);

        // Block edits on entries distributed from a standard scheme
        if ($entry->standard_scheme_entry_id) {
            return response()->json([
                'message' => 'This entry is managed by the standard scheme and cannot be edited individually.',
            ], 403);
        }

        $request->validate([
            'objective_ids' => ['present', 'array'],
            'objective_ids.*' => ['integer', 'exists:syllabus_objectives,id'],
        ]);

        $objectiveIds = collect($request->input('objective_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $result = DB::transaction(function () use ($scheme, $entry, $objectiveIds): array {
            $lockedScheme = SchemeOfWork::query()->lockForUpdate()->findOrFail($scheme->id);
            $lockedEntry = SchemeOfWorkEntry::query()
                ->where('scheme_of_work_id', $lockedScheme->id)
                ->lockForUpdate()
                ->findOrFail($entry->id);

            if (!$this->schemeAllowsEntryEditing($lockedScheme)) {
                return ['forbidden' => 'This scheme can no longer be edited in its current status.'];
            }

            if ($this->entryLockedForEditing($lockedScheme, $lockedEntry)) {
                return ['forbidden' => 'Completed entries cannot be edited on an approved scheme.'];
            }

            $this->assertObjectiveSelectionAllowed(
                $lockedScheme,
                $lockedEntry->syllabus_topic_id ? (int) $lockedEntry->syllabus_topic_id : null,
                $objectiveIds
            );

            $lockedEntry->objectives()->sync($objectiveIds);

            $objectives = $lockedEntry->objectives()
                ->with('topic')
                ->get(['syllabus_objectives.id', 'code', 'objective_text', 'cognitive_level']);

            return ['objectives' => $objectives];
        });

        if (isset($result['forbidden'])) {
            return response()->json(['message' => $result['forbidden']], 403);
        }

        return response()->json([
            'success' => true,
            'objectives' => $result['objectives'],
        ]);
    }

    private function entryLockedForEditing(SchemeOfWork $scheme, SchemeOfWorkEntry $entry): bool
    {
        return $scheme->status === 'approved' && $entry->status === 'completed';
    }

    private function schemeAllowsEntryEditing(SchemeOfWork $scheme): bool
    {
        return in_array($scheme->status, ['draft', 'revision_required', 'approved'], true);
    }

    private function assertObjectiveSelectionAllowed(SchemeOfWork $scheme, ?int $selectedTopicId, ?array $objectiveIds): void
    {
        if (!is_array($objectiveIds)) {
            return;
        }

        $objectiveIds = array_values(array_filter(array_map(static fn ($id) => (int) $id, $objectiveIds)));
        if (empty($objectiveIds)) {
            return;
        }

        $syllabus = $this->resolveActiveSyllabusForScheme($scheme);
        if (!$syllabus) {
            throw ValidationException::withMessages([
                'objective_ids' => 'No active syllabus is available for this scheme.',
            ]);
        }

        $topics = $syllabus->topics()->get(['id', 'name', 'description']);
        if (!$selectedTopicId) {
            throw ValidationException::withMessages([
                'syllabus_topic_id' => 'Select a syllabus topic before linking objectives.',
            ]);
        }

        /** @var SyllabusTopic|null $selectedTopic */
        $selectedTopic = $topics->firstWhere('id', $selectedTopicId);
        if (!$selectedTopic) {
            throw ValidationException::withMessages([
                'syllabus_topic_id' => 'The selected syllabus topic does not belong to this scheme syllabus.',
            ]);
        }

        $allowedTopicIds = $this->allowedTopicIds($selectedTopic, $topics);
        $validObjectiveCount = SyllabusObjective::query()
            ->whereIn('id', $objectiveIds)
            ->whereIn('syllabus_topic_id', $allowedTopicIds)
            ->count();

        if ($validObjectiveCount !== count($objectiveIds)) {
            throw ValidationException::withMessages([
                'objective_ids' => 'Linked objectives must belong to the selected syllabus topic or its imported descendants.',
            ]);
        }
    }

    private function resolveActiveSyllabusForScheme(SchemeOfWork $scheme): ?Syllabus
    {
        $gradeSubject = $scheme->gradeSubject;
        $subjectId = $gradeSubject?->subject_id;
        $gradeName = $gradeSubject?->grade?->name;

        if (!$subjectId || !$gradeName) {
            return null;
        }

        return Syllabus::query()
            ->where('subject_id', $subjectId)
            ->forGrade($gradeName)
            ->where('is_active', true)
            ->first();
    }

    private function allowedTopicIds(SyllabusTopic $selectedTopic, Collection $topics): array
    {
        $selectedContext = SyllabusStructureHelper::parseTopicDescription($selectedTopic->description);
        $selectedPath = $selectedContext['path'];
        if (empty($selectedPath)) {
            return [$selectedTopic->id];
        }

        $selectedKey = SyllabusStructureHelper::buildPlannerKey(
            $selectedContext['section_form'],
            $selectedContext['unit_id'],
            $selectedContext['unit_title'],
            $selectedPath
        );

        $allowedIds = $topics
            ->filter(function (SyllabusTopic $topic) use ($selectedTopic, $selectedContext, $selectedPath, $selectedKey): bool {
                if ((int) $topic->id === (int) $selectedTopic->id) {
                    return true;
                }

                $context = SyllabusStructureHelper::parseTopicDescription($topic->description);
                $candidatePath = $context['path'];
                if (empty($candidatePath)) {
                    return (int) $topic->id === (int) $selectedTopic->id;
                }

                $candidateKey = SyllabusStructureHelper::buildPlannerKey(
                    $context['section_form'],
                    $context['unit_id'],
                    $context['unit_title'],
                    $candidatePath
                );

                if ($candidateKey === $selectedKey) {
                    return true;
                }

                return $this->sameTopicContext($selectedContext, $context)
                    && $this->pathStartsWith($candidatePath, $selectedPath);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (empty($allowedIds)) {
            return [$selectedTopic->id];
        }

        return $allowedIds;
    }

    private function sameTopicContext(array $left, array $right): bool
    {
        return $this->normalizeComparison($left['section_form'] ?? null) === $this->normalizeComparison($right['section_form'] ?? null)
            && $this->normalizeComparison($left['unit_id'] ?? null) === $this->normalizeComparison($right['unit_id'] ?? null)
            && $this->normalizeComparison($left['unit_title'] ?? null) === $this->normalizeComparison($right['unit_title'] ?? null);
    }

    private function pathStartsWith(array $candidatePath, array $prefixPath): bool
    {
        if (count($candidatePath) < count($prefixPath)) {
            return false;
        }

        foreach ($prefixPath as $index => $segment) {
            if ($this->normalizeComparison($candidatePath[$index] ?? null) !== $this->normalizeComparison($segment)) {
                return false;
            }
        }

        return true;
    }

    private function normalizeComparison(?string $value): string
    {
        $normalized = mb_strtolower(trim((string) $value));
        $normalized = preg_replace('/[^\pL\pN]+/u', ' ', $normalized) ?? $normalized;

        return preg_replace('/\s+/', ' ', $normalized) ?? '';
    }
}

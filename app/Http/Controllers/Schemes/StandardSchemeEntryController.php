<?php

namespace App\Http\Controllers\Schemes;

use App\Http\Controllers\Controller;
use App\Models\Schemes\StandardScheme;
use App\Models\Schemes\StandardSchemeEntry;
use App\Models\Schemes\SyllabusObjective;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StandardSchemeEntryController extends Controller {

    public function update(Request $request, StandardScheme $standardScheme, StandardSchemeEntry $entry): JsonResponse {
        $this->authorize('update', $standardScheme);

        abort_if($entry->standard_scheme_id !== $standardScheme->id, 404);

        $validated = $request->validate([
            'syllabus_topic_id'  => ['sometimes', 'nullable', 'integer', 'exists:syllabus_topics,id'],
            'topic'              => ['sometimes', 'nullable', 'string', 'max:255'],
            'sub_topic'          => ['sometimes', 'nullable', 'string', 'max:255'],
            'learning_objectives' => ['sometimes', 'nullable', 'string'],
            'status'             => ['sometimes', 'string', 'in:planned,taught,completed,skipped'],
            'objective_ids'      => ['sometimes', 'array'],
            'objective_ids.*'    => ['integer', 'exists:syllabus_objectives,id'],
        ]);

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

        $result = DB::transaction(function () use ($standardScheme, $entry, $validated, $objectiveIds): array {
            $lockedScheme = StandardScheme::query()
                ->lockForUpdate()
                ->findOrFail($standardScheme->id);
            $lockedEntry = StandardSchemeEntry::query()
                ->where('standard_scheme_id', $lockedScheme->id)
                ->lockForUpdate()
                ->findOrFail($entry->id);

            if (!$lockedScheme->isEditable()) {
                return ['forbidden' => 'This standard scheme can no longer be edited in its current status.'];
            }

            if (array_key_exists('syllabus_topic_id', $validated)) {
                $validated['syllabus_topic_id'] = filled($validated['syllabus_topic_id'])
                    ? (int) $validated['syllabus_topic_id']
                    : null;
            }

            $lockedEntry->update($validated);

            if (is_array($objectiveIds)) {
                $lockedEntry->objectives()->sync($objectiveIds);
            }

            return [
                'entry' => $lockedEntry->fresh(['syllabusTopic', 'objectives']),
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

    public function syncObjectives(Request $request, StandardScheme $standardScheme, StandardSchemeEntry $entry): JsonResponse {
        $this->authorize('update', $standardScheme);

        abort_if($entry->standard_scheme_id !== $standardScheme->id, 404);

        $request->validate([
            'objective_ids'   => ['present', 'array'],
            'objective_ids.*' => ['integer', 'exists:syllabus_objectives,id'],
        ]);

        $objectiveIds = collect($request->input('objective_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $result = DB::transaction(function () use ($standardScheme, $entry, $objectiveIds): array {
            $lockedScheme = StandardScheme::query()
                ->lockForUpdate()
                ->findOrFail($standardScheme->id);
            $lockedEntry = StandardSchemeEntry::query()
                ->where('standard_scheme_id', $lockedScheme->id)
                ->lockForUpdate()
                ->findOrFail($entry->id);

            if (!$lockedScheme->isEditable()) {
                return ['forbidden' => 'This standard scheme can no longer be edited in its current status.'];
            }

            $lockedEntry->objectives()->sync($objectiveIds);

            $objectives = $lockedEntry->objectives()->get(['syllabus_objectives.id', 'code', 'objective_text', 'cognitive_level']);

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
}

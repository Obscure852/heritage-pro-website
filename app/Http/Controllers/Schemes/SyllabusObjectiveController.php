<?php

namespace App\Http\Controllers\Schemes;

use App\Http\Controllers\Controller;
use App\Models\Schemes\Syllabus;
use App\Models\Schemes\SyllabusObjective;
use App\Models\Schemes\SyllabusTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SyllabusObjectiveController extends Controller {
    public function store(Request $request, Syllabus $syllabus, SyllabusTopic $topic): JsonResponse {
        $this->authorize('edit-syllabi');

        abort_if($topic->syllabus_id !== $syllabus->id, 404);

        $validated = $request->validate([
            'sequence'        => ['required', 'integer', 'min:1'],
            'code'            => ['required', 'string', 'max:30'],
            'objective_text'  => ['required', 'string', 'max:2000'],
            'cognitive_level' => ['required', Rule::in(SyllabusController::cognitiveLevels())],
        ]);

        $objective = $topic->objectives()->create($validated);

        return response()->json($objective, 201);
    }

    public function update(Request $request, Syllabus $syllabus, SyllabusTopic $topic, SyllabusObjective $objective): JsonResponse {
        $this->authorize('edit-syllabi');

        abort_if($topic->syllabus_id !== $syllabus->id, 404);
        abort_if($objective->syllabus_topic_id !== $topic->id, 404);

        $validated = $request->validate([
            'sequence'        => ['required', 'integer', 'min:1'],
            'code'            => ['required', 'string', 'max:30'],
            'objective_text'  => ['required', 'string', 'max:2000'],
            'cognitive_level' => ['required', Rule::in(SyllabusController::cognitiveLevels())],
        ]);

        $objective->update($validated);

        return response()->json($objective);
    }

    public function destroy(Syllabus $syllabus, SyllabusTopic $topic, SyllabusObjective $objective): JsonResponse {
        $this->authorize('edit-syllabi');

        abort_if($topic->syllabus_id !== $syllabus->id, 404);
        abort_if($objective->syllabus_topic_id !== $topic->id, 404);

        $objective->delete();

        return response()->json(['deleted' => true]);
    }
}

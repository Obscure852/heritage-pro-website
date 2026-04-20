<?php

namespace App\Http\Controllers\Schemes;

use App\Http\Controllers\Controller;
use App\Models\Schemes\Syllabus;
use App\Models\Schemes\SyllabusTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyllabusTopicController extends Controller {
    public function store(Request $request, Syllabus $syllabus): JsonResponse {
        $this->authorize('edit-syllabi');

        $validated = $request->validate([
            'sequence'       => ['required', 'integer', 'min:1'],
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:5000'],
            'suggested_weeks' => ['nullable', 'integer', 'min:1', 'max:52'],
        ]);

        $topic = $syllabus->topics()->create($validated);

        return response()->json($topic, 201);
    }

    public function update(Request $request, Syllabus $syllabus, SyllabusTopic $topic): JsonResponse {
        $this->authorize('edit-syllabi');

        abort_if($topic->syllabus_id !== $syllabus->id, 404);

        $validated = $request->validate([
            'sequence'       => ['required', 'integer', 'min:1'],
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:5000'],
            'suggested_weeks' => ['nullable', 'integer', 'min:1', 'max:52'],
        ]);

        $topic->update($validated);

        return response()->json($topic);
    }

    public function destroy(Syllabus $syllabus, SyllabusTopic $topic): JsonResponse {
        $this->authorize('edit-syllabi');

        abort_if($topic->syllabus_id !== $syllabus->id, 404);

        $topic->delete();

        return response()->json(['deleted' => true]);
    }
}

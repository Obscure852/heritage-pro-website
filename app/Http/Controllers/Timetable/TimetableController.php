<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\Timetable\StoreTimetableRequest;
use App\Http\Requests\Timetable\UpdateTimetableRequest;
use App\Models\Term;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableVersion;
use App\Services\Timetable\TimetablePublishingService;
use App\Services\Timetable\TimetableService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TimetableController extends Controller {
    protected TimetableService $timetableService;
    protected TimetablePublishingService $publishingService;

    public function __construct(TimetableService $timetableService, TimetablePublishingService $publishingService) {
        $this->timetableService = $timetableService;
        $this->publishingService = $publishingService;
    }

    /**
     * List all timetables for the current term.
     */
    public function index(Request $request): View {
        $query = Timetable::with(['creator', 'term']);

        // Non-admin users (Teachers, HODs) only see published timetables
        if (Gate::denies('manage-timetable')) {
            $query->published();
        }

        $timetables = $query->orderBy('created_at', 'desc')->get();
        $terms = Term::orderBy('id', 'desc')->get();

        return view('timetable.index', compact('timetables', 'terms'));
    }

    /**
     * Create a new timetable.
     */
    public function store(StoreTimetableRequest $request): JsonResponse {
        $timetable = $this->timetableService->create(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'data' => $timetable,
            'message' => 'Timetable created successfully',
        ], 201);
    }

    /**
     * Show a single timetable with relationships.
     */
    public function show(Timetable $timetable): JsonResponse {
        $timetable = $this->timetableService->find($timetable->id);

        return response()->json(['data' => $timetable]);
    }

    /**
     * Update an existing timetable.
     */
    public function update(UpdateTimetableRequest $request, Timetable $timetable): JsonResponse {
        $timetable = $this->timetableService->update($timetable, $request->validated());

        return response()->json([
            'data' => $timetable,
            'message' => 'Timetable updated successfully',
        ]);
    }

    /**
     * Soft-delete a timetable.
     */
    public function destroy(Timetable $timetable): JsonResponse {
        $this->timetableService->delete($timetable);

        return response()->json(['message' => 'Timetable deleted successfully']);
    }

    /**
     * Publish a timetable (set to published, create version snapshot, archive previous).
     */
    public function publish(Timetable $timetable): JsonResponse {
        try {
            $timetable = $this->publishingService->publish($timetable, auth()->id());

            return response()->json([
                'data' => $timetable,
                'message' => 'Timetable published successfully. It is now the active schedule.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Unpublish a timetable (revert to draft).
     */
    public function unpublish(Timetable $timetable): JsonResponse {
        try {
            $timetable = $this->publishingService->unpublish($timetable, auth()->id());

            return response()->json([
                'data' => $timetable,
                'message' => 'Timetable reverted to draft status.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Show version history for a timetable.
     */
    public function versions(Timetable $timetable): View {
        $versions = TimetableVersion::where('timetable_id', $timetable->id)
            ->with('publisher')
            ->orderBy('version_number', 'desc')
            ->get();

        return view('timetable.versions.index', compact('timetable', 'versions'));
    }

    /**
     * Rollback to a previous version (restore slots, set to draft).
     */
    public function rollback(Timetable $timetable, TimetableVersion $version): JsonResponse {
        try {
            $timetable = $this->publishingService->rollback($timetable, $version->id, auth()->id());

            return response()->json([
                'data' => $timetable,
                'message' => "Restored to version {$version->version_number}. Timetable is now in draft status.",
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}

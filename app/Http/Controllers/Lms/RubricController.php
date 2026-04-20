<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\Rubric;
use App\Models\Lms\RubricCriterion;
use App\Models\Lms\RubricLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Exception;

class RubricController extends Controller {
    /**
     * Display a listing of rubrics.
     */
    public function index() {
        Gate::authorize('manage-lms-courses');

        $rubrics = Rubric::with('creator')
            ->withCount(['criteria', 'assignments'])
            ->where(function ($query) {
                $query->where('is_template', true)
                    ->orWhere('created_by', Auth::id());
            })
            ->orderBy('title')
            ->get();

        return view('lms.rubrics.index', compact('rubrics'));
    }

    /**
     * Show the form for creating a new rubric.
     */
    public function create() {
        Gate::authorize('manage-lms-courses');

        return view('lms.rubrics.create');
    }

    /**
     * Store a newly created rubric.
     */
    public function store(Request $request) {
        Gate::authorize('manage-lms-courses');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_template' => 'boolean',
        ]);

        try {
            $rubric = Rubric::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'is_template' => $validated['is_template'] ?? false,
                'created_by' => Auth::id(),
                'total_points' => 0,
            ]);

            return redirect()->route('lms.rubrics.edit', $rubric)
                ->with('success', 'Rubric created. Now add grading criteria.');
        } catch (Exception $e) {
            Log::error('Failed to create rubric', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return back()->withInput()
                ->with('error', 'Failed to create rubric. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified rubric.
     */
    public function edit(Rubric $rubric) {
        Gate::authorize('manage-lms-courses');

        $rubric->load(['criteria.levels']);
        $assignmentCount = $rubric->assignments()->count();

        return view('lms.rubrics.edit', compact('rubric', 'assignmentCount'));
    }

    /**
     * Update the specified rubric.
     */
    public function update(Request $request, Rubric $rubric) {
        Gate::authorize('manage-lms-courses');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_template' => 'boolean',
            'criteria_json' => 'required|string',
        ]);

        // Parse criteria from JSON
        $criteriaData = json_decode($validated['criteria_json'], true);

        if (!is_array($criteriaData) || count($criteriaData) === 0) {
            return back()->withInput()->with('error', 'Please add at least one criterion.');
        }

        try {
            DB::beginTransaction();

            // Update rubric basic info
            $rubric->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'is_template' => $validated['is_template'] ?? false,
            ]);

            // Track existing IDs
            $existingCriterionIds = [];

            $totalPoints = 0;
            foreach ($criteriaData as $criterionIndex => $criterionData) {
                if (!empty($criterionData['id'])) {
                    // Update existing criterion
                    $criterion = RubricCriterion::find($criterionData['id']);
                    if ($criterion && $criterion->rubric_id === $rubric->id) {
                        $criterion->update([
                            'title' => $criterionData['title'],
                            'description' => $criterionData['description'] ?? null,
                            'max_points' => $criterionData['max_points'],
                            'sequence' => $criterionIndex,
                        ]);
                        $existingCriterionIds[] = $criterion->id;
                    }
                } else {
                    // Create new criterion
                    $criterion = RubricCriterion::create([
                        'rubric_id' => $rubric->id,
                        'title' => $criterionData['title'],
                        'description' => $criterionData['description'] ?? null,
                        'max_points' => $criterionData['max_points'],
                        'sequence' => $criterionIndex,
                    ]);
                    $existingCriterionIds[] = $criterion->id;
                }

                $totalPoints += $criterionData['max_points'];

                // Track level IDs for this criterion
                $existingLevelIds = [];

                foreach ($criterionData['levels'] as $levelIndex => $levelData) {
                    if (!empty($levelData['id'])) {
                        // Update existing level
                        $level = RubricLevel::find($levelData['id']);
                        if ($level && $level->criterion_id === $criterion->id) {
                            $level->update([
                                'title' => $levelData['title'],
                                'description' => $levelData['description'] ?? null,
                                'points' => $levelData['points'],
                                'sequence' => $levelIndex,
                            ]);
                            $existingLevelIds[] = $level->id;
                        }
                    } else {
                        // Create new level
                        $level = RubricLevel::create([
                            'criterion_id' => $criterion->id,
                            'title' => $levelData['title'],
                            'description' => $levelData['description'] ?? null,
                            'points' => $levelData['points'],
                            'sequence' => $levelIndex,
                        ]);
                        $existingLevelIds[] = $level->id;
                    }
                }

                // Delete removed levels for this criterion
                RubricLevel::where('criterion_id', $criterion->id)
                    ->whereNotIn('id', $existingLevelIds)
                    ->delete();
            }

            // Delete removed criteria (and their levels via cascade)
            RubricCriterion::where('rubric_id', $rubric->id)
                ->whereNotIn('id', $existingCriterionIds)
                ->delete();

            // Update total points
            $rubric->update(['total_points' => $totalPoints]);

            DB::commit();

            return redirect()->route('lms.settings.index')
                ->with('success', 'Rubric updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update rubric', [
                'error' => $e->getMessage(),
                'rubric_id' => $rubric->id,
                'user_id' => Auth::id()
            ]);

            return back()->withInput()
                ->with('error', 'Failed to update rubric. Please try again.');
        }
    }

    /**
     * Remove the specified rubric.
     */
    public function destroy(Rubric $rubric) {
        Gate::authorize('manage-lms-courses');

        $assignmentCount = $rubric->assignments()->count();

        if ($assignmentCount > 0) {
            return back()->with('error',
                "Cannot delete this rubric because it is used by {$assignmentCount} assignment(s). " .
                "Remove the rubric from those assignments first, or duplicate this rubric to create a new version."
            );
        }

        try {
            $rubric->delete();

            return redirect()->route('lms.rubrics.index')
                ->with('success', 'Rubric deleted successfully.');
        } catch (Exception $e) {
            Log::error('Failed to delete rubric', [
                'error' => $e->getMessage(),
                'rubric_id' => $rubric->id,
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Failed to delete rubric. Please try again.');
        }
    }

    /**
     * Duplicate a rubric.
     */
    public function duplicate(Rubric $rubric) {
        Gate::authorize('manage-lms-courses');

        try {
            $rubric->load(['criteria.levels']);
            $newRubric = $rubric->duplicate();
            $newRubric->update(['created_by' => Auth::id()]);

            return redirect()->route('lms.rubrics.edit', $newRubric)
                ->with('success', 'Rubric duplicated successfully. You can now edit the copy.');
        } catch (Exception $e) {
            Log::error('Failed to duplicate rubric', [
                'error' => $e->getMessage(),
                'rubric_id' => $rubric->id,
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Failed to duplicate rubric. Please try again.');
        }
    }

    /**
     * Show the form for creating a new criterion.
     */
    public function createCriterion(Rubric $rubric) {
        Gate::authorize('manage-lms-courses');

        return view('lms.rubrics.criteria.create', compact('rubric'));
    }

    /**
     * Store a newly created criterion.
     */
    public function storeCriterion(Request $request, Rubric $rubric) {
        Gate::authorize('manage-lms-courses');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_points' => 'required|numeric|min:1|max:1000',
            'levels' => 'required|array|min:2',
            'levels.*.title' => 'required|string|max:255',
            'levels.*.description' => 'nullable|string',
            'levels.*.points' => 'required|numeric|min:0|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $maxSequence = $rubric->criteria()->max('sequence') ?? -1;

            $criterion = RubricCriterion::create([
                'rubric_id' => $rubric->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'max_points' => $validated['max_points'],
                'sequence' => $maxSequence + 1,
            ]);

            foreach ($validated['levels'] as $index => $levelData) {
                RubricLevel::create([
                    'criterion_id' => $criterion->id,
                    'title' => $levelData['title'],
                    'description' => $levelData['description'] ?? null,
                    'points' => $levelData['points'],
                    'sequence' => $index,
                ]);
            }

            // Update rubric total points
            $rubric->update([
                'total_points' => $rubric->criteria()->sum('max_points')
            ]);

            DB::commit();

            return redirect()->route('lms.rubrics.edit', $rubric)
                ->with('success', 'Criterion added successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create criterion', [
                'error' => $e->getMessage(),
                'rubric_id' => $rubric->id,
                'user_id' => Auth::id()
            ]);

            return back()->withInput()
                ->with('error', 'Failed to add criterion. Please try again.');
        }
    }

    /**
     * Show the form for editing a criterion.
     */
    public function editCriterion(Rubric $rubric, RubricCriterion $criterion) {
        Gate::authorize('manage-lms-courses');

        if ($criterion->rubric_id !== $rubric->id) {
            abort(404);
        }

        $criterion->load('levels');

        return view('lms.rubrics.criteria.edit', compact('rubric', 'criterion'));
    }

    /**
     * Update the specified criterion.
     */
    public function updateCriterion(Request $request, Rubric $rubric, RubricCriterion $criterion) {
        Gate::authorize('manage-lms-courses');

        if ($criterion->rubric_id !== $rubric->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_points' => 'required|numeric|min:1|max:1000',
            'levels' => 'required|array|min:2',
            'levels.*.id' => 'nullable|integer',
            'levels.*.title' => 'required|string|max:255',
            'levels.*.description' => 'nullable|string',
            'levels.*.points' => 'required|numeric|min:0|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $criterion->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'max_points' => $validated['max_points'],
            ]);

            $existingLevelIds = [];

            foreach ($validated['levels'] as $index => $levelData) {
                if (!empty($levelData['id'])) {
                    $level = RubricLevel::find($levelData['id']);
                    if ($level && $level->criterion_id === $criterion->id) {
                        $level->update([
                            'title' => $levelData['title'],
                            'description' => $levelData['description'] ?? null,
                            'points' => $levelData['points'],
                            'sequence' => $index,
                        ]);
                        $existingLevelIds[] = $level->id;
                    }
                } else {
                    $level = RubricLevel::create([
                        'criterion_id' => $criterion->id,
                        'title' => $levelData['title'],
                        'description' => $levelData['description'] ?? null,
                        'points' => $levelData['points'],
                        'sequence' => $index,
                    ]);
                    $existingLevelIds[] = $level->id;
                }
            }

            // Delete removed levels
            RubricLevel::where('criterion_id', $criterion->id)
                ->whereNotIn('id', $existingLevelIds)
                ->delete();

            // Update rubric total points
            $rubric->update([
                'total_points' => $rubric->criteria()->sum('max_points')
            ]);

            DB::commit();

            return redirect()->route('lms.rubrics.edit', $rubric)
                ->with('success', 'Criterion updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update criterion', [
                'error' => $e->getMessage(),
                'criterion_id' => $criterion->id,
                'user_id' => Auth::id()
            ]);

            return back()->withInput()
                ->with('error', 'Failed to update criterion. Please try again.');
        }
    }

    /**
     * Remove the specified criterion.
     */
    public function destroyCriterion(Rubric $rubric, RubricCriterion $criterion) {
        Gate::authorize('manage-lms-courses');

        if ($criterion->rubric_id !== $rubric->id) {
            abort(404);
        }

        try {
            $criterion->delete();

            // Update rubric total points
            $rubric->update([
                'total_points' => $rubric->criteria()->sum('max_points')
            ]);

            return redirect()->route('lms.rubrics.edit', $rubric)
                ->with('success', 'Criterion deleted successfully.');
        } catch (Exception $e) {
            Log::error('Failed to delete criterion', [
                'error' => $e->getMessage(),
                'criterion_id' => $criterion->id,
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Failed to delete criterion. Please try again.');
        }
    }
}

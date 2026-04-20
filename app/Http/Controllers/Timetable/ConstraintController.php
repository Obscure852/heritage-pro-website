<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\Timetable\SaveConsecutiveLimitRequest;
use App\Http\Requests\Timetable\SavePeriodRestrictionRequest;
use App\Http\Requests\Timetable\SaveRoomRequirementRequest;
use App\Http\Requests\Timetable\SaveSubjectPairRequest;
use App\Http\Requests\Timetable\SaveTeacherRoomAssignmentRequest;
use App\Http\Requests\Timetable\SaveSubjectSpreadRequest;
use App\Http\Requests\Timetable\SaveTeacherAvailabilityRequest;
use App\Http\Requests\Timetable\SaveTeacherPreferenceRequest;
use App\Models\Klass;
use App\Models\OptionalSubject;
use App\Models\Subject;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableConstraint;
use App\Models\Timetable\TimetableSetting;
use App\Models\User;
use App\Models\Venue;
use App\Services\Timetable\ConstraintService;
use App\Services\Timetable\PeriodSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConstraintController extends Controller {
    private ConstraintService $constraintService;
    private PeriodSettingsService $periodSettingsService;

    public function __construct(ConstraintService $constraintService, PeriodSettingsService $periodSettingsService) {
        $this->constraintService = $constraintService;
        $this->periodSettingsService = $periodSettingsService;
    }

    /**
     * Display the constraints configuration page for a timetable.
     */
    public function index(Timetable $timetable): View {
        $teachers = User::teachingAndCurrent()
            ->select('id', 'firstname', 'lastname', 'department')
            ->orderBy('firstname')
            ->get();

        $subjects = Subject::orderBy('name')->get();
        $venues = Venue::orderBy('name')->get();
        $venueTypes = Venue::distinct()->pluck('type');

        $constraints = TimetableConstraint::where('timetable_id', $timetable->id)
            ->active()
            ->get()
            ->groupBy('constraint_type');

        $subjectSpreadConstraints = TimetableConstraint::where('timetable_id', $timetable->id)
            ->where('constraint_type', TimetableConstraint::TYPE_SUBJECT_SPREAD)
            ->get();

        $hasActiveLessonSpreadRules = $subjectSpreadConstraints->contains(function (TimetableConstraint $constraint): bool {
            $config = (array) ($constraint->constraint_config ?? []);
            return $constraint->is_active && array_key_exists('max_lessons_per_day', $config);
        });

        $hasResetLegacySpreadRules = $subjectSpreadConstraints->contains(function (TimetableConstraint $constraint): bool {
            $config = (array) ($constraint->constraint_config ?? []);
            return !$constraint->is_active
                && array_key_exists('max_periods_per_day', $config)
                && !array_key_exists('max_lessons_per_day', $config);
        });

        $showSubjectSpreadResetNotice = $hasResetLegacySpreadRules && !$hasActiveLessonSpreadRules;

        $klasses = Klass::where('active', true)
            ->where('term_id', session('selected_term_id'))
            ->orderBy('name')
            ->get();

        $periodDefinitions = $this->periodSettingsService->getPeriodDefinitions();
        $periodsPerDay = TimetableSetting::get('periods_per_day', 7);

        // Load coupling groups from TimetableSetting (where they're actually stored)
        $couplingGroups = TimetableSetting::get('optional_coupling_groups', []);

        // Load OptionalSubject names for display in coupling groups summary
        $optionalSubjectNames = [];
        if (!empty($couplingGroups)) {
            $allSubjectIds = collect($couplingGroups)
                ->pluck('optional_subject_ids')
                ->flatten()
                ->unique()
                ->filter()
                ->values()
                ->toArray();

            if (!empty($allSubjectIds)) {
                $optionalSubjectNames = OptionalSubject::with('gradeSubject.subject')
                    ->whereIn('id', $allSubjectIds)
                    ->get()
                    ->mapWithKeys(fn($os) => [
                        $os->id => $os->gradeSubject?->subject?->name ?? $os->name,
                    ])
                    ->toArray();
            }
        }

        return view('timetable.constraints.index', compact(
            'timetable',
            'teachers',
            'subjects',
            'venues',
            'venueTypes',
            'klasses',
            'constraints',
            'periodDefinitions',
            'periodsPerDay',
            'couplingGroups',
            'optionalSubjectNames',
            'showSubjectSpreadResetNotice'
        ));
    }

    /**
     * Save teacher availability constraint (unavailable slots).
     */
    public function saveTeacherAvailability(SaveTeacherAvailabilityRequest $request): JsonResponse {
        $validated = $request->validated();

        try {
            $this->constraintService->saveTeacherAvailability(
                $validated['timetable_id'],
                $validated['teacher_id'],
                $validated['unavailable_slots']
            );

            return response()->json([
                'success' => true,
                'message' => 'Teacher availability saved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving teacher availability: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save teacher time preference constraint (morning/afternoon).
     * Derives preferred_periods server-side from period definitions.
     */
    public function saveTeacherPreference(SaveTeacherPreferenceRequest $request): JsonResponse {
        $validated = $request->validated();

        try {
            $timetableId = $validated['timetable_id'];
            $teacherId = $validated['teacher_id'];
            $preference = $validated['preference'];

            // If preference is 'none', delete the constraint
            if ($preference === 'none') {
                $this->constraintService->deleteTeacherPreference($timetableId, $teacherId);

                return response()->json([
                    'success' => true,
                    'message' => 'Teacher preference removed successfully.',
                ]);
            }

            // Derive preferred_periods from period definitions
            $periodDefinitions = $this->periodSettingsService->getPeriodDefinitions();
            $totalPeriods = count($periodDefinitions);
            $midpoint = (int) ceil($totalPeriods / 2);

            $preferredPeriods = [];
            if ($preference === 'morning') {
                // First half of periods (e.g., periods 1-4 out of 7)
                for ($i = 1; $i <= $midpoint; $i++) {
                    $preferredPeriods[] = $i;
                }
            } elseif ($preference === 'afternoon') {
                // Second half of periods (e.g., periods 5-7 out of 7)
                for ($i = $midpoint + 1; $i <= $totalPeriods; $i++) {
                    $preferredPeriods[] = $i;
                }
            }

            $this->constraintService->saveTeacherPreference(
                $timetableId,
                $teacherId,
                $preference,
                $preferredPeriods
            );

            return response()->json([
                'success' => true,
                'message' => 'Teacher preference saved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving teacher preference: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save room requirement constraint (subject requires specific venue type).
     */
    public function saveRoomRequirement(SaveRoomRequirementRequest $request): JsonResponse {
        $validated = $request->validated();

        try {
            $this->constraintService->saveRoomRequirement(
                $validated['timetable_id'],
                $validated['subject_id'],
                $validated['required_venue_type']
            );

            return response()->json([
                'success' => true,
                'message' => 'Room requirement saved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving room requirement: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save room capacity setting (global toggle + enforcement level).
     */
    public function saveRoomCapacity(Request $request): JsonResponse {
        $validated = $request->validate([
            'timetable_id' => 'required|integer|exists:timetables,id',
            'enabled' => 'required|boolean',
            'enforcement' => 'required|in:strict,warn_only',
        ]);

        try {
            $this->constraintService->saveRoomCapacitySetting(
                $validated['timetable_id'],
                $validated['enabled'],
                $validated['enforcement']
            );

            return response()->json([
                'success' => true,
                'message' => 'Room capacity setting saved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving room capacity setting: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save subject spread constraint (max lessons per day for a subject).
     */
    public function saveSubjectSpread(SaveSubjectSpreadRequest $request): JsonResponse {
        $validated = $request->validated();

        try {
            $maxLessonsPerDay = (int) ($validated['max_lessons_per_day'] ?? ($validated['max_periods_per_day'] ?? 0));

            $constraint = $this->constraintService->saveSubjectSpread(
                $validated['timetable_id'],
                $validated['subject_id'],
                $maxLessonsPerDay,
                $validated['distribute_across_cycle'] ?? true
            );

            return response()->json([
                'success' => true,
                'message' => 'Subject spread constraint saved successfully.',
                'constraint_id' => $constraint->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving subject spread constraint: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save consecutive period limit constraint for a teacher or global default.
     */
    public function saveConsecutiveLimit(SaveConsecutiveLimitRequest $request): JsonResponse {
        $validated = $request->validated();

        try {
            $this->constraintService->saveConsecutiveLimit(
                $validated['timetable_id'],
                $validated['teacher_id'] ?? null,
                $validated['max_consecutive_periods']
            );

            return response()->json([
                'success' => true,
                'message' => 'Consecutive limit saved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving consecutive limit: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save subject pair constraint (relationship rule between two subjects).
     */
    public function saveSubjectPair(SaveSubjectPairRequest $request): JsonResponse {
        $validated = $request->validated();

        try {
            $constraint = $this->constraintService->saveSubjectPair(
                $validated['timetable_id'],
                $validated['subject_id_a'],
                $validated['subject_id_b'],
                $validated['klass_id'] ?? null,
                $validated['rule']
            );

            return response()->json([
                'success' => true,
                'message' => 'Subject pair rule saved successfully.',
                'constraint_id' => $constraint->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving subject pair rule: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save period restriction constraint (time-of-day rules for a subject).
     * Derives allowed_periods server-side for first_or_last and afternoon_only.
     */
    public function savePeriodRestriction(SavePeriodRestrictionRequest $request): JsonResponse {
        $validated = $request->validated();

        try {
            $timetableId = $validated['timetable_id'];
            $subjectId = $validated['subject_id'];
            $restriction = $validated['restriction'];

            $periodDefinitions = $this->periodSettingsService->getPeriodDefinitions();
            $totalPeriods = count($periodDefinitions);

            // Derive allowed_periods based on restriction type
            $allowedPeriods = match ($restriction) {
                'first_or_last' => [1, $totalPeriods],
                'afternoon_only' => range((int) ceil($totalPeriods / 2) + 1, $totalPeriods),
                default => array_map('intval', $validated['allowed_periods'] ?? []),
            };

            if (empty($allowedPeriods)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please specify at least one allowed period.',
                ], 422);
            }

            $constraint = $this->constraintService->savePeriodRestriction(
                $timetableId,
                $subjectId,
                $restriction,
                $allowedPeriods
            );

            return response()->json([
                'success' => true,
                'message' => 'Period restriction saved successfully.',
                'constraint_id' => $constraint->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving period restriction: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save teacher room assignment constraint (home room).
     */
    public function saveTeacherRoomAssignment(SaveTeacherRoomAssignmentRequest $request): JsonResponse {
        $validated = $request->validated();

        try {
            $constraint = $this->constraintService->saveTeacherRoomAssignment(
                $validated['timetable_id'],
                $validated['teacher_id'],
                $validated['venue_id']
            );

            $message = 'Teacher room assignment saved successfully.';

            // Check for duplicate venue assignments (two teachers sharing the same home room)
            $duplicateCount = TimetableConstraint::where('timetable_id', $validated['timetable_id'])
                ->active()
                ->ofType(TimetableConstraint::TYPE_TEACHER_ROOM_ASSIGNMENT)
                ->whereRaw("JSON_EXTRACT(constraint_config, '$.venue_id') = ?", [(int) $validated['venue_id']])
                ->count();

            if ($duplicateCount > 1) {
                $message .= ' Warning: ' . $duplicateCount . ' teachers share this room — venue conflicts will occur during generation.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'constraint_id' => $constraint->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving teacher room assignment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a constraint by ID.
     * Validates the constraint belongs to a draft timetable the user manages.
     */
    public function deleteConstraint(Request $request, TimetableConstraint $constraint): JsonResponse {
        try {
            // Verify the constraint's timetable is still in draft status
            $timetable = Timetable::find($constraint->timetable_id);
            if (!$timetable || $timetable->status !== Timetable::STATUS_DRAFT) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete constraints from a published or archived timetable.',
                ], 403);
            }

            $constraint->delete();

            return response()->json([
                'success' => true,
                'message' => 'Constraint deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting constraint: ' . $e->getMessage(),
            ], 500);
        }
    }
}

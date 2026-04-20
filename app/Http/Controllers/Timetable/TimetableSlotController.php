<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\Timetable\StoreSlotRequest;
use App\Models\Grade;
use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableSlot;
use App\Models\User;
use App\Services\Timetable\PeriodSettingsService;
use App\Services\Timetable\SlotManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimetableSlotController extends Controller {
    public function __construct(
        protected SlotManagementService $slotManagementService,
        protected PeriodSettingsService $periodSettingsService
    ) {}

    /**
     * Display the stacked grid page for a timetable.
     */
    public function index(Request $request, Timetable $timetable): View {
        $grades = Grade::with(['klasses' => function ($q) {
            $q->where('term_id', session('selected_term_id'))
              ->orderBy('name');
        }])->whereHas('klasses', function ($q) {
            $q->where('term_id', session('selected_term_id'));
        })->orderBy('sequence')->get();

        $daySchedule = $this->periodSettingsService->getDaySchedule();

        return view('timetable.slots.grid', compact('timetable', 'grades', 'daySchedule'));
    }

    /**
     * Return JSON grid data for a timetable, optionally filtered by class.
     */
    public function gridData(Request $request, Timetable $timetable): JsonResponse {
        try {
            $klassId = $request->query('klass_id');

            $grid = $this->slotManagementService->getGridData(
                $timetable->id,
                $klassId ? (int) $klassId : null
            );

            return response()->json([
                'success' => true,
                'grid' => $grid,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading grid data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign a slot (single or block) to the timetable.
     *
     * Accepts either klass_subject_id (existing allocation) or
     * grade_subject_id + teacher_id + klass_id (auto-creates KlassSubject).
     */
    public function assign(StoreSlotRequest $request): JsonResponse {
        try {
            $validated = $request->validated();

            // --- Optional subject path: create slot directly ---
            if (!empty($validated['optional_subject_id'])) {
                $optionalSubject = OptionalSubject::findOrFail($validated['optional_subject_id']);
                $teacherId = $validated['teacher_id'] ?? $optionalSubject->user_id;
                $klassId = (int) ($validated['klass_id'] ?? 0);
                $gradeId = (int) ($optionalSubject->grade_id ?? 0);
                $blockSize = (int) ($validated['block_size'] ?? 1);
                $couplingGroupKey = $this->resolveOptionalCouplingGroupKey(
                    (int) $validated['timetable_id'],
                    (int) $optionalSubject->id
                );

                if ($blockSize > 1) {
                    $breakError = $this->slotManagementService->validateBlockPlacement(
                        (int) $validated['period_number'],
                        $blockSize
                    );

                    if ($breakError) {
                        return response()->json([
                            'success' => false,
                            'errors' => [$breakError],
                        ], 409);
                    }
                }

                if ($couplingGroupKey !== null) {
                    $couplingError = $this->slotManagementService->validateCouplingPlacement(
                        (int) $validated['timetable_id'],
                        $couplingGroupKey,
                        (int) $validated['day_of_cycle'],
                        (int) $validated['period_number']
                    );
                    if ($couplingError !== null) {
                        return response()->json([
                            'success' => false,
                            'errors' => [$couplingError],
                        ], 409);
                    }
                }

                // Detect conflicts using the service (teacher + class double-booking)
                $periods = range($validated['period_number'], $validated['period_number'] + $blockSize - 1);
                $errors = [];

                foreach ($periods as $periodNumber) {
                    $conflicts = $this->slotManagementService->detectConflicts(
                        $validated['timetable_id'],
                        $teacherId,
                        $klassId,
                        $validated['day_of_cycle'],
                        $periodNumber,
                        null,
                        null,
                        [],
                        $gradeId,
                        true,
                        (int) $optionalSubject->id
                    );
                    if (!empty($conflicts)) {
                        $errors = array_merge($errors, $conflicts);
                    }
                }

                if (!empty($errors)) {
                    return response()->json([
                        'success' => false,
                        'errors' => $errors,
                    ], 409);
                }

                $blockId = $blockSize > 1 ? \Illuminate\Support\Str::uuid()->toString() : null;
                $createdSlots = [];

                $optionalVenueId = $optionalSubject->venue_id ?: null;

                foreach ($periods as $periodNumber) {
                    $createdSlots[] = TimetableSlot::create([
                        'timetable_id' => $validated['timetable_id'],
                        'klass_subject_id' => null,
                        'optional_subject_id' => $optionalSubject->id,
                        'teacher_id' => $teacherId,
                        'venue_id' => $optionalVenueId,
                        'day_of_cycle' => $validated['day_of_cycle'],
                        'period_number' => $periodNumber,
                        'duration' => $blockSize,
                        'is_locked' => false,
                        'block_id' => $blockId,
                        'coupling_group_key' => $couplingGroupKey,
                    ]);
                }

                $this->slotManagementService->invalidateTimetableCaches((int) $validated['timetable_id']);

                return response()->json([
                    'success' => true,
                    'slots' => $createdSlots,
                    'message' => 'Optional subject slot assigned successfully.',
                ], 201);
            }

            // --- Standard path: KlassSubject or GradeSubject ---
            $klassSubjectId = $validated['klass_subject_id'] ?? null;

            if (!$klassSubjectId && !empty($validated['grade_subject_id'])) {
                $gradeSubject = \App\Models\GradeSubject::findOrFail($validated['grade_subject_id']);
                $klass = \App\Models\Klass::findOrFail($validated['klass_id']);

                $searchCriteria = [
                    'klass_id' => $klass->id,
                    'grade_subject_id' => $gradeSubject->id,
                    'term_id' => session('selected_term_id'),
                ];

                $klassSubject = KlassSubject::firstOrCreate(
                    $searchCriteria,
                    [
                        'user_id' => $validated['teacher_id'],
                        'grade_id' => $klass->grade_id,
                        'year' => date('Y'),
                        'active' => true,
                    ]
                );

                $klassSubjectId = $klassSubject->id;

                // Update teacher if it changed
                if ($klassSubject->user_id !== (int) $validated['teacher_id']) {
                    KlassSubject::where('id', $klassSubjectId)
                        ->update(['user_id' => $validated['teacher_id']]);
                }
            }

            $result = $this->slotManagementService->assignSlot(
                $validated['timetable_id'],
                $klassSubjectId,
                $validated['day_of_cycle'],
                $validated['period_number'],
                $validated['block_size'] ?? 1
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'errors' => $result['errors'],
                ], 409);
            }

            return response()->json([
                'success' => true,
                'slots' => $result['slots'],
                'message' => 'Slot assigned successfully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error assigning slot: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a slot or entire block.
     */
    public function delete(Request $request, TimetableSlot $slot): JsonResponse {
        try {
            $result = $this->slotManagementService->deleteSlot($slot->id);

            return response()->json([
                'success' => true,
                'deleted_count' => $result['deleted_count'],
                'message' => "Deleted {$result['deleted_count']} slot(s) successfully.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting slot: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check conflicts without saving — read-only preview.
     *
     * Accepts either klass_subject_id (resolves teacher/class from it)
     * or teacher_id + klass_id directly (for grade_subject fallback).
     */
    public function checkConflicts(Request $request): JsonResponse {
        $request->validate([
            'timetable_id' => ['required', 'integer', 'exists:timetables,id'],
            'klass_subject_id' => ['nullable', 'integer', 'exists:klass_subject,id'],
            'optional_subject_id' => ['nullable', 'integer', 'exists:optional_subjects,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'klass_id' => ['nullable', 'integer', 'exists:klasses,id'],
            'day_of_cycle' => ['required', 'integer', 'min:1', 'max:6'],
            'period_number' => ['required', 'integer', 'min:1'],
            'block_size' => ['sometimes', 'integer', 'in:1,2,3'],
            'exclude_slot_id' => ['sometimes', 'integer', 'exists:timetable_slots,id'],
        ]);

        try {
            $gradeId = null;
            $isOptional = false;
            $couplingGroupKey = null;
            if ($request->input('klass_subject_id')) {
                $klassSubject = KlassSubject::findOrFail($request->input('klass_subject_id'));
                $teacherId = $klassSubject->user_id;
                $klassId = $klassSubject->klass_id;
                $gradeId = (int) ($klassSubject->grade_id ?? 0);
            } elseif ($request->input('optional_subject_id')) {
                $optionalSubject = OptionalSubject::findOrFail($request->input('optional_subject_id'));
                $teacherId = (int) $request->input('teacher_id');
                $klassId = (int) $request->input('klass_id');
                $gradeId = (int) ($optionalSubject->grade_id ?? 0);
                $isOptional = true;
                $couplingGroupKey = $this->resolveOptionalCouplingGroupKey(
                    (int) $request->input('timetable_id'),
                    (int) $optionalSubject->id
                );

                if (!$teacherId || !$klassId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Teacher and class are required for optional subject conflict checks.',
                    ], 422);
                }
            } else {
                $teacherId = (int) $request->input('teacher_id');
                $klassId = (int) $request->input('klass_id');
                if ($klassId > 0) {
                    $klass = \App\Models\Klass::find($klassId);
                    $gradeId = (int) ($klass?->grade_id ?? 0);
                }

                if (!$teacherId || !$klassId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Either klass_subject_id or both teacher_id and klass_id are required.',
                    ], 422);
                }
            }
            $blockSize = $request->input('block_size', 1);
            $excludeSlotId = $request->input('exclude_slot_id');
            $klassSubjectId = $request->input('klass_subject_id') ? (int) $request->input('klass_subject_id') : null;
            $optionalSubjectId = $request->input('optional_subject_id') ? (int) $request->input('optional_subject_id') : null;
            $excludeSlotIds = [];
            if ($excludeSlotId !== null) {
                $excludeSlotIds[] = (int) $excludeSlotId;
            }

            if ($blockSize > 1) {
                $blockError = $this->slotManagementService->validateBlockPlacement(
                    (int) $request->input('period_number'),
                    (int) $blockSize
                );
                if ($blockError !== null) {
                    return response()->json([
                        'success' => true,
                        'has_conflicts' => true,
                        'conflicts' => [$blockError],
                        'warnings' => [],
                    ]);
                }
            }

            if ($optionalSubjectId !== null && $couplingGroupKey !== null) {
                $couplingError = $this->slotManagementService->validateCouplingPlacement(
                    (int) $request->input('timetable_id'),
                    $couplingGroupKey,
                    (int) $request->input('day_of_cycle'),
                    (int) $request->input('period_number'),
                    $excludeSlotIds
                );

                if ($couplingError !== null) {
                    return response()->json([
                        'success' => true,
                        'has_conflicts' => true,
                        'conflicts' => [$couplingError],
                        'warnings' => [],
                    ]);
                }
            }

            $periods = range(
                $request->input('period_number'),
                $request->input('period_number') + $blockSize - 1
            );

            $allConflicts = [];

            foreach ($periods as $periodNumber) {
                $conflicts = $this->slotManagementService->detectConflicts(
                    $request->input('timetable_id'),
                    $teacherId,
                    $klassId,
                    $request->input('day_of_cycle'),
                    $periodNumber,
                    $excludeSlotId,
                    $klassSubjectId,
                    [],
                    $gradeId,
                    $isOptional,
                    $optionalSubjectId
                );

                if (!empty($conflicts)) {
                    $allConflicts = array_merge($allConflicts, $conflicts);
                }
            }

            // Also collect soft warnings for UI display
            $softWarnings = [];
            $subjectId = null;
            if ($klassSubjectId) {
                $ks = KlassSubject::with('gradeSubject')->find($klassSubjectId);
                $subjectId = $ks?->gradeSubject?->subject_id;
            }

            foreach ($periods as $periodNumber) {
                $warnings = $this->slotManagementService->getConstraintWarnings(
                    $request->input('timetable_id'),
                    $teacherId,
                    $klassId,
                    $request->input('day_of_cycle'),
                    $periodNumber,
                    $subjectId
                );
                $softWarnings = array_merge($softWarnings, $warnings);
            }

            $softWarnings = collect($softWarnings)
                ->unique(function (array $warning): string {
                    $type = (string) ($warning['constraint_type'] ?? '');
                    $message = (string) ($warning['message'] ?? '');
                    return "{$type}|{$message}";
                })
                ->values()
                ->all();

            return response()->json([
                'success' => true,
                'has_conflicts' => !empty($allConflicts),
                'conflicts' => $allConflicts,
                'warnings' => $softWarnings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking conflicts: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Move a single slot to a new day/period position.
     */
    public function move(Request $request): JsonResponse {
        $request->validate([
            'slot_id' => ['required', 'integer', 'exists:timetable_slots,id'],
            'target_day' => ['required', 'integer', 'min:1', 'max:6'],
            'target_period' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $result = $this->slotManagementService->moveSlot(
                (int) $request->input('slot_id'),
                (int) $request->input('target_day'),
                (int) $request->input('target_period')
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'errors' => $result['errors'],
                ], 409);
            }

            return response()->json([
                'success' => true,
                'message' => 'Slot moved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error moving slot: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Swap positions of two single slots.
     */
    public function swap(Request $request): JsonResponse {
        $request->validate([
            'slot_id_a' => ['required', 'integer', 'exists:timetable_slots,id'],
            'slot_id_b' => ['required', 'integer', 'exists:timetable_slots,id'],
        ]);

        try {
            $result = $this->slotManagementService->swapSlots(
                (int) $request->input('slot_id_a'),
                (int) $request->input('slot_id_b')
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'errors' => $result['errors'],
                ], 409);
            }

            return response()->json([
                'success' => true,
                'message' => 'Slots swapped successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error swapping slots: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle lock/unlock state of a slot (or entire block).
     */
    public function toggleLock(Request $request): JsonResponse {
        $request->validate([
            'slot_id' => ['required', 'integer', 'exists:timetable_slots,id'],
        ]);

        try {
            $result = $this->slotManagementService->toggleLock(
                (int) $request->input('slot_id')
            );

            $label = $result['is_locked'] ? 'locked' : 'unlocked';

            return response()->json([
                'success' => true,
                'is_locked' => $result['is_locked'],
                'count' => $result['count'],
                'message' => "Slot {$label} successfully.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling lock: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return soft constraint warnings for a potential move/swap target position.
     * Used by the grid UI to show warnings before committing a DnD operation.
     */
    public function getWarnings(Request $request): JsonResponse {
        $request->validate([
            'timetable_id' => ['required', 'integer', 'exists:timetables,id'],
            'slot_id' => ['required', 'integer', 'exists:timetable_slots,id'],
            'target_day' => ['required', 'integer', 'min:1', 'max:6'],
            'target_period' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $slot = TimetableSlot::findOrFail($request->input('slot_id'));
            $subjectId = null;
            $klassId = null;

            if ($slot->klass_subject_id) {
                $klassSubject = KlassSubject::with('gradeSubject')->findOrFail($slot->klass_subject_id);
                $subjectId = $klassSubject->gradeSubject?->subject_id;
                $klassId = $klassSubject->klass_id;
            } elseif ($slot->optional_subject_id) {
                $optionalSubject = OptionalSubject::with('gradeSubject')->find($slot->optional_subject_id);
                $subjectId = $optionalSubject?->gradeSubject?->subject_id;
                // Resolve klass_id from timetable context (slot doesn't store it directly)
                $klassId = null;
            }

            $warnings = $this->slotManagementService->getConstraintWarnings(
                (int) $request->input('timetable_id'),
                $slot->teacher_id,
                $klassId,
                (int) $request->input('target_day'),
                (int) $request->input('target_period'),
                $subjectId
            );

            return response()->json([
                'success' => true,
                'warnings' => $warnings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking warnings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get allocation status: planned vs used vs remaining for a class-subject.
     */
    public function allocationStatus(Request $request): JsonResponse {
        $request->validate([
            'timetable_id' => ['required', 'integer', 'exists:timetables,id'],
            'klass_id' => ['sometimes', 'integer', 'exists:klasses,id'],
        ]);

        try {
            $status = $this->slotManagementService->getAllocationStatus(
                (int) $request->input('timetable_id'),
                $request->input('klass_id') ? (int) $request->input('klass_id') : null
            );

            return response()->json([
                'success' => true,
                'allocations' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading allocation status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return all teaching staff for the teacher dropdown.
     */
    public function teachers(Request $request): JsonResponse {
        try {
            $teachers = User::teachingAndCurrent()
                ->select('id', 'firstname', 'lastname')
                ->orderBy('firstname')
                ->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'name' => $t->firstname . ' ' . $t->lastname,
                ]);

            return response()->json([
                'success' => true,
                'teachers' => $teachers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading teachers: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resolve a coupling key for an optional subject when an existing keyed group is present.
     */
    private function resolveOptionalCouplingGroupKey(int $timetableId, int $optionalSubjectId): ?string {
        $keys = TimetableSlot::where('timetable_id', $timetableId)
            ->where('optional_subject_id', $optionalSubjectId)
            ->whereNotNull('coupling_group_key')
            ->pluck('coupling_group_key')
            ->map(fn($key) => trim((string) $key))
            ->filter(fn($key) => $key !== '')
            ->unique()
            ->values();

        if ($keys->count() === 1) {
            return (string) $keys->first();
        }

        return null;
    }

    /**
     * Return subjects for a specific class.
     *
     * Always loads all GradeSubjects for the grade. Where a KlassSubject
     * exists (class-level teacher assignment), returns that with its teacher.
     * Where no KlassSubject exists yet, returns the GradeSubject so the
     * timetable is usable before academic allocations are complete.
     * Also includes OptionalSubjects for the grade.
     */
    public function subjects(Request $request): JsonResponse {
        $request->validate([
            'klass_id' => ['required', 'integer', 'exists:klasses,id'],
        ]);

        try {
            $klass = \App\Models\Klass::findOrFail($request->input('klass_id'));
            $termId = session('selected_term_id');

            // Load all GradeSubjects for this grade/term
            $gradeSubjects = \App\Models\GradeSubject::where('grade_id', $klass->grade_id)
                ->where('term_id', $termId)
                ->with('subject')
                ->get();

            // Load existing KlassSubjects for this class/term, keyed by grade_subject_id
            $klassSubjects = KlassSubject::where('klass_id', $klass->id)
                ->where('term_id', $termId)
                ->with(['gradeSubject.subject', 'teacher'])
                ->get()
                ->keyBy('grade_subject_id');

            // Merge: use KlassSubject where it exists, GradeSubject otherwise
            $results = $gradeSubjects->map(function ($gs) use ($klassSubjects) {
                $ks = $klassSubjects->get($gs->id);

                if ($ks) {
                    return [
                        'id' => $ks->id,
                        'type' => 'klass_subject',
                        'subject_name' => $gs->subject?->name ?? 'Unknown',
                        'teacher_id' => $ks->user_id,
                        'teacher_name' => $ks->teacher?->full_name,
                    ];
                }

                return [
                    'id' => $gs->id,
                    'type' => 'grade_subject',
                    'subject_name' => $gs->subject?->name ?? 'Unknown',
                    'teacher_id' => null,
                    'teacher_name' => null,
                ];
            });

            // 2. OptionalSubjects for this grade
            $optionalSubjects = \App\Models\OptionalSubject::where('grade_id', $klass->grade_id)
                ->where('term_id', $termId)
                ->with(['gradeSubject.subject', 'teacher'])
                ->get();

            $optionals = $optionalSubjects->map(fn($os) => [
                'id' => $os->id,
                'type' => 'optional_subject',
                'subject_name' => ($os->gradeSubject?->subject?->name ?? $os->name) . ' (Optional)',
                'teacher_id' => $os->user_id,
                'teacher_name' => $os->teacher?->full_name,
            ]);

            $subjects = $results->concat($optionals)->values();

            return response()->json([
                'success' => true,
                'subjects' => $subjects,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading subjects: ' . $e->getMessage(),
            ], 500);
        }
    }
}

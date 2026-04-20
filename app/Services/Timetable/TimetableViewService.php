<?php

namespace App\Services\Timetable;

use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableSlot;
use Illuminate\Support\Facades\Gate;

class TimetableViewService {
    public function __construct(
        protected SlotManagementService $slotManagementService,
        protected PeriodSettingsService $periodSettingsService
    ) {}

    /**
     * Resolve which timetable a user can view.
     * Admin: any timetable. Non-admin: published only.
     */
    public function resolveViewableTimetable(?int $timetableId = null): ?Timetable {
        if ($timetableId && Gate::allows('manage-timetable')) {
            return Timetable::findOrFail($timetableId);
        }

        if ($timetableId && Gate::denies('manage-timetable')) {
            $timetable = Timetable::findOrFail($timetableId);
            if (!$timetable->isPublished()) {
                abort(403, 'This timetable is not published.');
            }
            return $timetable;
        }

        // No ID: return published timetable for current term
        return Timetable::published()
            ->where('term_id', session('selected_term_id'))
            ->first();
    }

    /**
     * Get class grid data — delegates to SlotManagementService.
     */
    public function getClassGridData(int $timetableId, int $klassId): array {
        return $this->slotManagementService->getGridData($timetableId, $klassId);
    }

    /**
     * Get teacher-pivoted grid data: all slots for a teacher across all classes.
     */
    public function getTeacherGridData(int $timetableId, int $teacherId): array {
        $slots = TimetableSlot::where('timetable_id', $timetableId)
            ->where('teacher_id', $teacherId)
            ->with([
                'klassSubject.klass',
                'klassSubject.gradeSubject.subject',
                'optionalSubject.gradeSubject.subject',
                'teacher',
                'venue',
            ])
            ->orderBy('day_of_cycle')
            ->orderBy('period_number')
            ->get();

        $grid = [];

        foreach ($slots as $slot) {
            $day = $slot->day_of_cycle;
            $period = $slot->period_number;

            if ($slot->optional_subject_id) {
                $subjectName = $slot->optionalSubject?->gradeSubject?->subject?->name
                    ?? 'Optional';
            } else {
                $subjectName = $slot->klassSubject?->gradeSubject?->subject?->name ?? 'Unknown';
            }

            if (isset($grid[$day][$period])) {
                // Teacher has multiple commitments at same day/period (e.g. coupled electives).
                // Append subject and class names rather than silently overwriting.
                $grid[$day][$period]['subject_name'] .= ' / ' . $subjectName;
                $className = $slot->klassSubject?->klass?->name ?? '';
                if ($className && !str_contains($grid[$day][$period]['class_name'], $className)) {
                    $grid[$day][$period]['class_name'] .= ' / ' . $className;
                }
            } else {
                $grid[$day][$period] = [
                    'id' => $slot->id,
                    'subject_name' => $subjectName,
                    'class_name' => $slot->klassSubject?->klass?->name ?? '',
                    'klass_subject_id' => $slot->klass_subject_id,
                    'teacher_name' => $slot->teacher?->full_name ?? '',
                    'venue_name' => $slot->venue?->name,
                    'duration' => $slot->duration,
                    'block_id' => $slot->block_id,
                ];
            }
        }

        return $grid;
    }

    /**
     * Get master grid data: all slots grouped by class.
     */
    public function getMasterGridData(int $timetableId): array {
        $slots = TimetableSlot::where('timetable_id', $timetableId)
            ->with([
                'klassSubject.klass.grade',
                'klassSubject.gradeSubject.subject',
                'optionalSubject.gradeSubject.subject',
                'teacher',
                'venue',
            ])
            ->orderBy('day_of_cycle')
            ->orderBy('period_number')
            ->get();

        $classGrids = [];
        $classInfo = [];

        foreach ($slots as $slot) {
            $klassId = $slot->klassSubject?->klass_id;
            if (!$klassId) continue;

            if (!isset($classInfo[$klassId])) {
                $klass = $slot->klassSubject->klass;
                $classInfo[$klassId] = [
                    'name' => $klass->name ?? '',
                    'grade_id' => $klass->grade_id ?? 0,
                    'grade_name' => $klass->grade?->name ?? '',
                    'grade_sequence' => $klass->grade?->sequence ?? 0,
                ];
            }

            $day = $slot->day_of_cycle;
            $period = $slot->period_number;

            if ($slot->optional_subject_id) {
                $subject = $slot->optionalSubject?->gradeSubject?->subject;
            } else {
                $subject = $slot->klassSubject?->gradeSubject?->subject;
            }

            $subjectAbbrev = $subject?->abbrev ?? mb_substr($subject?->name ?? '?', 0, 3);

            $classGrids[$klassId][$day][$period] = [
                'subject_abbrev' => $subjectAbbrev,
                'teacher_initials' => $this->getInitials($slot->teacher?->full_name ?? ''),
                'venue_name' => $slot->venue?->name,
                'klass_subject_id' => $slot->klass_subject_id,
                'duration' => $slot->duration,
                'block_id' => $slot->block_id,
            ];
        }

        return ['grids' => $classGrids, 'classes' => $classInfo];
    }

    /**
     * Get initials from a full name (e.g. "John Smith" -> "JS").
     */
    private function getInitials(string $name): string {
        $parts = preg_split('/\s+/', trim($name));
        if (empty($parts) || $parts[0] === '') return '?';

        $first = mb_strtoupper(mb_substr($parts[0], 0, 1));
        $last = count($parts) > 1 ? mb_strtoupper(mb_substr(end($parts), 0, 1)) : '';

        return $first . $last;
    }
}

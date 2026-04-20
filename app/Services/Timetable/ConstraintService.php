<?php

namespace App\Services\Timetable;

use App\Models\Timetable\TimetableConstraint;
use Illuminate\Support\Collection;

class ConstraintService {
    // ==================== TEACHER AVAILABILITY (CONST-01) ====================

    /**
     * Get active teacher availability constraints for a timetable.
     *
     * @param int $timetableId
     * @return Collection
     */
    public function getTeacherAvailabilities(int $timetableId): Collection {
        return TimetableConstraint::where('timetable_id', $timetableId)
            ->active()
            ->ofType(TimetableConstraint::TYPE_TEACHER_AVAILABILITY)
            ->get();
    }

    /**
     * Save (upsert) a teacher availability constraint.
     *
     * @param int $timetableId
     * @param int $teacherId
     * @param array $unavailableSlots Array of [day_of_cycle, period_number]
     * @return TimetableConstraint
     */
    public function saveTeacherAvailability(int $timetableId, int $teacherId, array $unavailableSlots): TimetableConstraint {
        $existing = $this->findByJsonField(
            $timetableId,
            TimetableConstraint::TYPE_TEACHER_AVAILABILITY,
            'teacher_id',
            (int) $teacherId
        );

        $config = [
            'teacher_id' => (int) $teacherId,
            'unavailable_slots' => $unavailableSlots,
        ];

        if ($existing) {
            $existing->update([
                'constraint_config' => $config,
                'is_active' => true,
            ]);
            return $existing;
        }

        return TimetableConstraint::create([
            'timetable_id' => $timetableId,
            'constraint_type' => TimetableConstraint::TYPE_TEACHER_AVAILABILITY,
            'constraint_config' => $config,
            'is_hard' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Delete a teacher availability constraint.
     *
     * @param int $timetableId
     * @param int $teacherId
     * @return bool
     */
    public function deleteTeacherAvailability(int $timetableId, int $teacherId): bool {
        $constraint = $this->findByJsonField(
            $timetableId,
            TimetableConstraint::TYPE_TEACHER_AVAILABILITY,
            'teacher_id',
            (int) $teacherId
        );

        return $constraint ? (bool) $constraint->delete() : false;
    }

    // ==================== TEACHER PREFERENCE (CONST-02) ====================

    /**
     * Get active teacher preference constraints for a timetable.
     *
     * @param int $timetableId
     * @return Collection
     */
    public function getTeacherPreferences(int $timetableId): Collection {
        return TimetableConstraint::where('timetable_id', $timetableId)
            ->active()
            ->ofType(TimetableConstraint::TYPE_TEACHER_PREFERENCE)
            ->get();
    }

    /**
     * Save (upsert) a teacher preference constraint.
     *
     * @param int $timetableId
     * @param int $teacherId
     * @param string $preference 'morning'|'afternoon'|'none'
     * @param array $preferredPeriods
     * @return TimetableConstraint
     */
    public function saveTeacherPreference(int $timetableId, int $teacherId, string $preference, array $preferredPeriods = []): TimetableConstraint {
        $existing = $this->findByJsonField(
            $timetableId,
            TimetableConstraint::TYPE_TEACHER_PREFERENCE,
            'teacher_id',
            (int) $teacherId
        );

        $config = [
            'teacher_id' => (int) $teacherId,
            'preference' => $preference,
            'preferred_periods' => $preferredPeriods,
        ];

        if ($existing) {
            $existing->update([
                'constraint_config' => $config,
                'is_active' => true,
            ]);
            return $existing;
        }

        return TimetableConstraint::create([
            'timetable_id' => $timetableId,
            'constraint_type' => TimetableConstraint::TYPE_TEACHER_PREFERENCE,
            'constraint_config' => $config,
            'is_hard' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Delete a teacher preference constraint.
     *
     * @param int $timetableId
     * @param int $teacherId
     * @return bool
     */
    public function deleteTeacherPreference(int $timetableId, int $teacherId): bool {
        $constraint = $this->findByJsonField(
            $timetableId,
            TimetableConstraint::TYPE_TEACHER_PREFERENCE,
            'teacher_id',
            (int) $teacherId
        );

        return $constraint ? (bool) $constraint->delete() : false;
    }

    // ==================== ROOM REQUIREMENT (CONST-03) ====================

    /**
     * Get active room requirement constraints for a timetable.
     *
     * @param int $timetableId
     * @return Collection
     */
    public function getRoomRequirements(int $timetableId): Collection {
        return TimetableConstraint::where('timetable_id', $timetableId)
            ->active()
            ->ofType(TimetableConstraint::TYPE_ROOM_REQUIREMENT)
            ->get();
    }

    /**
     * Save (upsert) a room requirement constraint.
     *
     * @param int $timetableId
     * @param int $subjectId
     * @param string $requiredVenueType
     * @return TimetableConstraint
     */
    public function saveRoomRequirement(int $timetableId, int $subjectId, string $requiredVenueType): TimetableConstraint {
        $existing = $this->findByJsonField(
            $timetableId,
            TimetableConstraint::TYPE_ROOM_REQUIREMENT,
            'subject_id',
            (int) $subjectId
        );

        $config = [
            'subject_id' => (int) $subjectId,
            'required_venue_type' => $requiredVenueType,
        ];

        if ($existing) {
            $existing->update([
                'constraint_config' => $config,
                'is_active' => true,
            ]);
            return $existing;
        }

        return TimetableConstraint::create([
            'timetable_id' => $timetableId,
            'constraint_type' => TimetableConstraint::TYPE_ROOM_REQUIREMENT,
            'constraint_config' => $config,
            'is_hard' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Delete a room requirement constraint.
     *
     * @param int $timetableId
     * @param int $subjectId
     * @return bool
     */
    public function deleteRoomRequirement(int $timetableId, int $subjectId): bool {
        $constraint = $this->findByJsonField(
            $timetableId,
            TimetableConstraint::TYPE_ROOM_REQUIREMENT,
            'subject_id',
            (int) $subjectId
        );

        return $constraint ? (bool) $constraint->delete() : false;
    }

    // ==================== ROOM CAPACITY (CONST-04) ====================

    /**
     * Get the room capacity constraint for a timetable.
     *
     * @param int $timetableId
     * @return TimetableConstraint|null
     */
    public function getRoomCapacitySetting(int $timetableId): ?TimetableConstraint {
        return TimetableConstraint::where('timetable_id', $timetableId)
            ->active()
            ->ofType(TimetableConstraint::TYPE_ROOM_CAPACITY)
            ->first();
    }

    /**
     * Save (upsert) the room capacity constraint.
     *
     * @param int $timetableId
     * @param bool $enabled
     * @param string $enforcement 'strict'|'warn_only'
     * @return TimetableConstraint
     */
    public function saveRoomCapacitySetting(int $timetableId, bool $enabled, string $enforcement = 'strict'): TimetableConstraint {
        $existing = TimetableConstraint::where('timetable_id', $timetableId)
            ->ofType(TimetableConstraint::TYPE_ROOM_CAPACITY)
            ->first();

        $config = [
            'enabled' => $enabled,
            'enforcement' => $enforcement,
        ];

        if ($existing) {
            $existing->update([
                'constraint_config' => $config,
                'is_active' => true,
            ]);
            return $existing;
        }

        return TimetableConstraint::create([
            'timetable_id' => $timetableId,
            'constraint_type' => TimetableConstraint::TYPE_ROOM_CAPACITY,
            'constraint_config' => $config,
            'is_hard' => true,
            'is_active' => true,
        ]);
    }

    // ==================== SUBJECT SPREAD (CONST-05) ====================

    /**
     * Get active subject spread constraints for a timetable.
     *
     * @param int $timetableId
     * @return Collection
     */
    public function getSubjectSpreads(int $timetableId): Collection {
        return TimetableConstraint::where('timetable_id', $timetableId)
            ->active()
            ->ofType(TimetableConstraint::TYPE_SUBJECT_SPREAD)
            ->get();
    }

    /**
     * Save (upsert) a subject spread constraint.
     *
     * @param int $timetableId
     * @param int $subjectId
     * @param int $maxLessonsPerDay
     * @param bool $distributeAcrossCycle
     * @return TimetableConstraint
     */
    public function saveSubjectSpread(int $timetableId, int $subjectId, int $maxLessonsPerDay, bool $distributeAcrossCycle = true): TimetableConstraint {
        $existing = $this->findByJsonField(
            $timetableId,
            TimetableConstraint::TYPE_SUBJECT_SPREAD,
            'subject_id',
            (int) $subjectId
        );

        $config = [
            'subject_id' => (int) $subjectId,
            'max_lessons_per_day' => $maxLessonsPerDay,
            'distribute_across_cycle' => $distributeAcrossCycle,
        ];

        if ($existing) {
            $existing->update([
                'constraint_config' => $config,
                'is_active' => true,
            ]);
            return $existing;
        }

        return TimetableConstraint::create([
            'timetable_id' => $timetableId,
            'constraint_type' => TimetableConstraint::TYPE_SUBJECT_SPREAD,
            'constraint_config' => $config,
            'is_hard' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Delete a subject spread constraint.
     *
     * @param int $timetableId
     * @param int $subjectId
     * @return bool
     */
    public function deleteSubjectSpread(int $timetableId, int $subjectId): bool {
        $constraint = $this->findByJsonField(
            $timetableId,
            TimetableConstraint::TYPE_SUBJECT_SPREAD,
            'subject_id',
            (int) $subjectId
        );

        return $constraint ? (bool) $constraint->delete() : false;
    }

    // ==================== CONSECUTIVE LIMIT (CONST-06) ====================

    /**
     * Get active consecutive limit constraints for a timetable.
     *
     * @param int $timetableId
     * @return Collection
     */
    public function getConsecutiveLimits(int $timetableId): Collection {
        return TimetableConstraint::where('timetable_id', $timetableId)
            ->active()
            ->ofType(TimetableConstraint::TYPE_CONSECUTIVE_LIMIT)
            ->get();
    }

    /**
     * Save (upsert) a consecutive limit constraint.
     *
     * @param int $timetableId
     * @param int|null $teacherId Null means global default
     * @param int $maxConsecutivePeriods
     * @return TimetableConstraint
     */
    public function saveConsecutiveLimit(int $timetableId, ?int $teacherId, int $maxConsecutivePeriods): TimetableConstraint {
        $existing = $this->findByJsonField(
            $timetableId,
            TimetableConstraint::TYPE_CONSECUTIVE_LIMIT,
            'teacher_id',
            $teacherId !== null ? (int) $teacherId : null
        );

        $config = [
            'teacher_id' => $teacherId !== null ? (int) $teacherId : null,
            'max_consecutive_periods' => $maxConsecutivePeriods,
        ];

        if ($existing) {
            $existing->update([
                'constraint_config' => $config,
                'is_active' => true,
            ]);
            return $existing;
        }

        return TimetableConstraint::create([
            'timetable_id' => $timetableId,
            'constraint_type' => TimetableConstraint::TYPE_CONSECUTIVE_LIMIT,
            'constraint_config' => $config,
            'is_hard' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Delete a consecutive limit constraint.
     *
     * @param int $timetableId
     * @param int|null $teacherId
     * @return bool
     */
    public function deleteConsecutiveLimit(int $timetableId, ?int $teacherId): bool {
        $constraint = $this->findByJsonField(
            $timetableId,
            TimetableConstraint::TYPE_CONSECUTIVE_LIMIT,
            'teacher_id',
            $teacherId !== null ? (int) $teacherId : null
        );

        return $constraint ? (bool) $constraint->delete() : false;
    }

    // ==================== SUBJECT PAIR (CONST-08) ====================

    /**
     * Get active subject pair constraints for a timetable.
     */
    public function getSubjectPairs(int $timetableId): Collection {
        return TimetableConstraint::where('timetable_id', $timetableId)
            ->active()
            ->ofType(TimetableConstraint::TYPE_SUBJECT_PAIR)
            ->get();
    }

    /**
     * Save (upsert) a subject pair constraint.
     * Always stores the smaller subject_id as subject_id_a for consistency.
     */
    public function saveSubjectPair(int $timetableId, int $subjectIdA, int $subjectIdB, ?int $klassId, string $rule): TimetableConstraint {
        // Normalize: smaller ID first
        if ($subjectIdA > $subjectIdB) {
            [$subjectIdA, $subjectIdB] = [$subjectIdB, $subjectIdA];
        }

        $fields = [
            'subject_id_a' => $subjectIdA,
            'subject_id_b' => $subjectIdB,
            'klass_id' => $klassId,
        ];

        $existing = $this->findByJsonFields($timetableId, TimetableConstraint::TYPE_SUBJECT_PAIR, $fields);

        $config = [
            'subject_id_a' => $subjectIdA,
            'subject_id_b' => $subjectIdB,
            'klass_id' => $klassId,
            'rule' => $rule,
        ];

        if ($existing) {
            $existing->update([
                'constraint_config' => $config,
                'is_active' => true,
            ]);
            return $existing;
        }

        return TimetableConstraint::create([
            'timetable_id' => $timetableId,
            'constraint_type' => TimetableConstraint::TYPE_SUBJECT_PAIR,
            'constraint_config' => $config,
            'is_hard' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Delete a subject pair constraint.
     */
    public function deleteSubjectPair(int $timetableId, int $subjectIdA, int $subjectIdB, ?int $klassId): bool {
        if ($subjectIdA > $subjectIdB) {
            [$subjectIdA, $subjectIdB] = [$subjectIdB, $subjectIdA];
        }

        $fields = [
            'subject_id_a' => $subjectIdA,
            'subject_id_b' => $subjectIdB,
            'klass_id' => $klassId,
        ];

        $constraint = $this->findByJsonFields($timetableId, TimetableConstraint::TYPE_SUBJECT_PAIR, $fields);

        return $constraint ? (bool) $constraint->delete() : false;
    }

    // ==================== PERIOD RESTRICTION (CONST-09) ====================

    /**
     * Get active period restriction constraints for a timetable.
     */
    public function getPeriodRestrictions(int $timetableId): Collection {
        return TimetableConstraint::where('timetable_id', $timetableId)
            ->active()
            ->ofType(TimetableConstraint::TYPE_PERIOD_RESTRICTION)
            ->get();
    }

    /**
     * Save (upsert) a period restriction constraint.
     */
    public function savePeriodRestriction(int $timetableId, int $subjectId, string $restriction, array $allowedPeriods): TimetableConstraint {
        $existing = $this->findByJsonField(
            $timetableId,
            TimetableConstraint::TYPE_PERIOD_RESTRICTION,
            'subject_id',
            $subjectId
        );

        $config = [
            'subject_id' => $subjectId,
            'restriction' => $restriction,
            'allowed_periods' => array_map('intval', $allowedPeriods),
        ];

        if ($existing) {
            $existing->update([
                'constraint_config' => $config,
                'is_active' => true,
            ]);
            return $existing;
        }

        return TimetableConstraint::create([
            'timetable_id' => $timetableId,
            'constraint_type' => TimetableConstraint::TYPE_PERIOD_RESTRICTION,
            'constraint_config' => $config,
            'is_hard' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Delete a period restriction constraint.
     */
    public function deletePeriodRestriction(int $timetableId, int $subjectId): bool {
        $constraint = $this->findByJsonField(
            $timetableId,
            TimetableConstraint::TYPE_PERIOD_RESTRICTION,
            'subject_id',
            $subjectId
        );

        return $constraint ? (bool) $constraint->delete() : false;
    }

    // ==================== TEACHER ROOM ASSIGNMENT (CONST-10) ====================

    /**
     * Get active teacher room assignment constraints for a timetable.
     */
    public function getTeacherRoomAssignments(int $timetableId): Collection {
        return TimetableConstraint::where('timetable_id', $timetableId)
            ->active()
            ->ofType(TimetableConstraint::TYPE_TEACHER_ROOM_ASSIGNMENT)
            ->get();
    }

    /**
     * Save (upsert) a teacher room assignment constraint.
     */
    public function saveTeacherRoomAssignment(int $timetableId, int $teacherId, int $venueId): TimetableConstraint {
        $existing = $this->findByJsonField(
            $timetableId,
            TimetableConstraint::TYPE_TEACHER_ROOM_ASSIGNMENT,
            'teacher_id',
            (int) $teacherId
        );

        $config = [
            'teacher_id' => (int) $teacherId,
            'venue_id' => (int) $venueId,
        ];

        if ($existing) {
            $existing->update([
                'constraint_config' => $config,
                'is_active' => true,
            ]);
            return $existing;
        }

        return TimetableConstraint::create([
            'timetable_id' => $timetableId,
            'constraint_type' => TimetableConstraint::TYPE_TEACHER_ROOM_ASSIGNMENT,
            'constraint_config' => $config,
            'is_hard' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Delete a teacher room assignment constraint.
     */
    public function deleteTeacherRoomAssignment(int $timetableId, int $teacherId): bool {
        $constraint = $this->findByJsonField(
            $timetableId,
            TimetableConstraint::TYPE_TEACHER_ROOM_ASSIGNMENT,
            'teacher_id',
            (int) $teacherId
        );

        return $constraint ? (bool) $constraint->delete() : false;
    }

    // ==================== SHARED HELPERS ====================

    /**
     * Find a constraint by timetable_id + type + JSON field value.
     * Uses whereRaw with JSON_EXTRACT to avoid whereJsonContains integer/string gotcha.
     *
     * @param int $timetableId
     * @param string $type
     * @param string $jsonField
     * @param mixed $value
     * @return TimetableConstraint|null
     */
    private function findByJsonField(int $timetableId, string $type, string $jsonField, $value): ?TimetableConstraint {
        $query = TimetableConstraint::where('timetable_id', $timetableId)
            ->ofType($type);

        if ($value === null) {
            $query->whereRaw("JSON_EXTRACT(constraint_config, '$.{$jsonField}') IS NULL");
        } else {
            $query->whereRaw("JSON_EXTRACT(constraint_config, '$.{$jsonField}') = ?", [(int) $value]);
        }

        return $query->first();
    }

    /**
     * Find a constraint by multiple JSON field values (e.g. subject pair with two subject IDs).
     */
    private function findByJsonFields(int $timetableId, string $type, array $fields): ?TimetableConstraint {
        $query = TimetableConstraint::where('timetable_id', $timetableId)
            ->ofType($type);

        foreach ($fields as $field => $value) {
            if ($value === null) {
                $query->whereRaw("JSON_EXTRACT(constraint_config, '$.{$field}') IS NULL");
            } else {
                $query->whereRaw("JSON_EXTRACT(constraint_config, '$.{$field}') = ?", [(int) $value]);
            }
        }

        return $query->first();
    }
}

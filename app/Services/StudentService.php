<?php

namespace App\Services;

use App\Helpers\CacheHelper;
use App\Helpers\TermHelper;
use App\Models\Student;
use App\Models\StudentTerm;
use App\Models\Klass;
use App\Models\House;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class StudentService
{
    /**
     * Clear all student-related caches.
     * Call this after any student CRUD operation.
     */
    public function clearStudentCaches(?int $termId = null): void
    {
        $termId = $termId ?? TermHelper::getCurrentTerm()?->id;

        CacheHelper::forgetStudentsData();
        CacheHelper::forgetStudentsTermData();

        if ($termId) {
            CacheHelper::forgetStudentsCount($termId);
            CacheHelper::forgetStudentsDashboard($termId);
        }
    }

    /**
     * Clear caches related to house allocations.
     */
    public function clearHouseCaches(int $termId, ?int $houseId = null): void
    {
        if ($houseId) {
            CacheHelper::forgetUnallocatedHouseStudents($houseId, $termId);
        } else {
            CacheHelper::forgetUnallocatedHouseStudents($termId);
        }
    }

    /**
     * Log a student-related audit event.
     */
    public function logAudit(string $action, int $studentId, array $context = []): void
    {
        $defaultContext = [
            'student_id' => $studentId,
            'action' => $action,
            'performed_by' => auth()->id(),
            'performed_at' => now()->toIso8601String(),
            'ip_address' => request()->ip(),
        ];

        Log::channel('daily')->info("Student {$action}", array_merge($defaultContext, $context));
    }

    /**
     * Soft delete a student with all related cleanup.
     *
     * @throws Exception
     */
    public function deleteStudent(Student $student): array
    {
        $currentClass = $student->currentClass();

        if ($currentClass) {
            throw new Exception(
                "Cannot delete student. Please remove {$student->fullName} from {$currentClass->name} class first."
            );
        }

        $studentName = $student->fullName;
        $studentId = $student->id;
        $currentTerm = TermHelper::getCurrentTerm();

        DB::beginTransaction();
        try {
            // Soft delete term record for current term
            StudentTerm::where('student_id', $studentId)
                ->where('term_id', $currentTerm->id)
                ->delete();

            // Soft delete the student
            $student->delete();

            // Audit log
            $this->logAudit('deleted', $studentId, [
                'student_name' => $studentName,
                'term_id' => $currentTerm->id,
            ]);

            DB::commit();

            // Clear caches
            $this->clearStudentCaches($currentTerm->id);

            return [
                'success' => true,
                'message' => 'Student has been removed successfully!',
                'student_name' => $studentName,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting student: ' . $e->getMessage(), [
                'student_id' => $studentId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Bulk delete students.
     *
     * @throws Exception
     */
    public function deleteMultipleStudents(array $studentIds): array
    {
        $currentTerm = TermHelper::getCurrentTerm();
        $deletedCount = 0;
        $skippedCount = 0;
        $deletedNames = [];
        $skippedNames = [];

        DB::beginTransaction();
        try {
            foreach ($studentIds as $studentId) {
                $student = Student::find($studentId);

                if (!$student) {
                    continue;
                }

                $studentName = $student->fullName;
                $currentClass = $student->currentClass();

                if (!$currentClass) {
                    StudentTerm::where('student_id', $studentId)
                        ->where('term_id', $currentTerm->id)
                        ->delete();

                    $student->delete();

                    $deletedCount++;
                    $deletedNames[] = $studentName;
                } else {
                    $skippedCount++;
                    $skippedNames[] = $studentName . " (in {$currentClass->name})";
                }
            }

            // Audit log
            $this->logAudit('bulk_deleted', 0, [
                'deleted_count' => $deletedCount,
                'deleted_names' => $deletedNames,
                'skipped_count' => $skippedCount,
                'term_id' => $currentTerm->id,
            ]);

            DB::commit();

            // Clear caches
            $this->clearStudentCaches($currentTerm->id);

            return [
                'success' => true,
                'deleted_count' => $deletedCount,
                'skipped_count' => $skippedCount,
                'deleted_names' => $deletedNames,
                'skipped_names' => $skippedNames,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting multiple students: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Allocate a student to a house.
     *
     * @throws Exception
     */
    public function allocateToHouse(Student $student, House $house, int $termId): array
    {
        // Verify house belongs to selected term
        if ((int) $house->term_id !== $termId) {
            throw new Exception('Cannot allocate to a house from a different term.');
        }

        // Verify student is a Current student in the selected term
        $isCurrentStudent = $student->terms()
            ->where('student_term.term_id', $termId)
            ->where('student_term.status', Student::STATUS_CURRENT)
            ->exists();

        if (!$isCurrentStudent) {
            throw new Exception('Student is not a current student for this term.');
        }

        // Check if student is already allocated to a house for this term
        $existingHouse = $student->houses()->wherePivot('term_id', $termId)->first();
        if ($existingHouse) {
            throw new Exception('Student is already allocated to ' . $existingHouse->name . ' for this term.');
        }

        // Allocate the student
        $house->students()->attach($student->id, [
            'term_id' => $termId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Audit log
        $this->logAudit('house_allocated', $student->id, [
            'house_id' => $house->id,
            'house_name' => $house->name,
            'term_id' => $termId,
        ]);

        // Clear cache
        $this->clearHouseCaches($termId);

        return [
            'success' => true,
            'message' => "{$student->first_name} {$student->last_name} allocated to {$house->name} successfully.",
        ];
    }

    /**
     * Allocate a student to a class.
     *
     * @throws Exception
     */
    public function allocateToClass(Student $student, Klass $klass, int $termId): array
    {
        // Verify class belongs to selected term
        if ((int) $klass->term_id !== $termId) {
            throw new Exception('Cannot allocate to a class from a different term.');
        }

        // Check if student is already in a class for this term
        $existingClass = $student->classes()->wherePivot('term_id', $termId)->first();
        if ($existingClass) {
            throw new Exception('Student is already allocated to ' . $existingClass->name . ' for this term.');
        }

        // Allocate the student
        DB::table('klass_student')->insert([
            'student_id' => $student->id,
            'klass_id' => $klass->id,
            'term_id' => $termId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Audit log
        $this->logAudit('class_allocated', $student->id, [
            'klass_id' => $klass->id,
            'klass_name' => $klass->name,
            'term_id' => $termId,
        ]);

        // Clear cache
        $this->clearStudentCaches($termId);

        return [
            'success' => true,
            'message' => "{$student->first_name} {$student->last_name} allocated to {$klass->name} successfully.",
        ];
    }

    /**
     * Update student's term status.
     */
    public function updateTermStatus(Student $student, int $termId, string $status): bool
    {
        $validStatuses = [
            Student::STATUS_CURRENT,
            Student::STATUS_LEFT,
            Student::STATUS_SUSPENDED,
            Student::STATUS_GRADUATED,
        ];

        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid status: {$status}");
        }

        $updated = $student->terms()
            ->wherePivot('term_id', $termId)
            ->updateExistingPivot($termId, ['status' => $status]);

        if ($updated) {
            $this->logAudit('status_changed', $student->id, [
                'term_id' => $termId,
                'new_status' => $status,
            ]);

            $this->clearStudentCaches($termId);
        }

        return $updated > 0;
    }

    /**
     * Get statistics for students in a term.
     */
    public function getTermStatistics(int $termId): array
    {
        $baseQuery = Student::whereHas('terms', function ($query) use ($termId) {
            $query->where('student_term.term_id', $termId)
                  ->where('student_term.status', Student::STATUS_CURRENT);
        });

        $total = (clone $baseQuery)->count();
        $males = (clone $baseQuery)->where('gender', Student::GENDER_MALE)->count();
        $females = (clone $baseQuery)->where('gender', Student::GENDER_FEMALE)->count();

        $withoutClass = (clone $baseQuery)->whereDoesntHave('classes', function ($query) use ($termId) {
            $query->where('klass_student.term_id', $termId);
        })->count();

        $withoutHouse = (clone $baseQuery)->whereDoesntHave('houses', function ($query) use ($termId) {
            $query->wherePivot('term_id', $termId);
        })->count();

        return [
            'total' => $total,
            'males' => $males,
            'females' => $females,
            'without_class' => $withoutClass,
            'without_house' => $withoutHouse,
        ];
    }
}

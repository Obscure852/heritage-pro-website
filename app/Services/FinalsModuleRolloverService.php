<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Klass;
use App\Models\GradeSubject;
use App\Models\OptionalSubject;
use App\Models\House;
use App\Exceptions\RolloverException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinalsModuleRolloverService{
    public function rolloverGraduatingStudents($fromTerm, $toTerm): void{
        try {
            Log::info('Starting Finals module rollover for F3 students', [
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id
            ]);

            $graduatingGrades = Grade::where('term_id', $fromTerm->id)->where('year', $fromTerm->year)->where('promotion', 'Alumni')->get();
            if ($graduatingGrades->isEmpty()) {
                Log::info('No graduating grades found');
                return;
            }

            $graduatingGradeIds = $graduatingGrades->pluck('id')->toArray();
            $f3Students = Student::whereHas('terms', function ($query) use ($fromTerm, $graduatingGradeIds) {
                $query->where('student_term.term_id', $fromTerm->id)
                      ->where('student_term.status', 'Current')
                      ->whereIn('student_term.grade_id', $graduatingGradeIds);
            })->with(['classes' => function($query) use ($fromTerm) {
                $query->wherePivot('term_id', $fromTerm->id);
            }])->get();

            if ($f3Students->isEmpty()) {
                Log::info('No F3 students found');
                return;
            }


            $finalKlassMapping = $this->rolloverKlasses($fromTerm, $toTerm, $graduatingGradeIds);
            $finalGradeSubjectMapping = $this->rolloverGradeSubjects($fromTerm, $toTerm, $graduatingGradeIds);
            $this->rolloverKlassSubjects($fromTerm, $toTerm, $finalKlassMapping, $finalGradeSubjectMapping);
            $finalOptionalSubjectMapping = $this->rolloverOptionalSubjects($fromTerm, $toTerm, $graduatingGradeIds, $finalGradeSubjectMapping);
            $finalHouseMapping = $this->rolloverHouses($fromTerm, $toTerm);

            $finalStudentMapping = $this->rolloverStudents($f3Students, $fromTerm, $toTerm);
            $this->rolloverStudentKlassRelationships($f3Students, $fromTerm, $toTerm, $finalStudentMapping, $finalKlassMapping);
            $this->rolloverStudentOptionalSubjectRelationships($f3Students, $fromTerm, $toTerm, $finalStudentMapping, $finalOptionalSubjectMapping, $finalKlassMapping);
            $this->rolloverStudentHouseRelationships($f3Students, $fromTerm, $toTerm, $finalStudentMapping, $finalHouseMapping);

            Log::info('Finals module rollover completed', [
                'students' => $f3Students->count(),
                'klasses' => count($finalKlassMapping),
                'grade_subjects' => count($finalGradeSubjectMapping),
                'optional_subjects' => count($finalOptionalSubjectMapping),
                'houses' => count($finalHouseMapping)
            ]);

        } catch (\Exception $e) {
            Log::error('Finals module rollover error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new RolloverException("Finals module rollover failed: " . $e->getMessage());
        }
    }

    protected function rolloverKlasses($fromTerm, $toTerm, $graduatingGradeIds): array{
        $mapping = [];
        $klasses = Klass::where('term_id', $fromTerm->id)->where('year', $fromTerm->year)->whereIn('grade_id', $graduatingGradeIds)->get();
    
        Log::info('Rolling over klasses to finals module', [
            'fromTermId' => $fromTerm->id,
            'toTermId' => $toTerm->id,
            'graduatingGradeIds' => $graduatingGradeIds,
            'totalKlassesFound' => $klasses->count(),
            'activeKlassesOnly' => true
        ]);
    
        if ($klasses->isEmpty()) {
            Log::warning('No active klasses found for graduating grades', [
                'fromTermId' => $fromTerm->id,
                'graduatingGradeIds' => $graduatingGradeIds
            ]);
            return $mapping;
        }
    
        foreach ($klasses as $klass) {
            try {
                $finalKlassId = DB::table('final_klasses')->insertGetId([
                    'original_klass_id' => $klass->id,
                    'name' => $klass->name,
                    'user_id' => $klass->user_id,
                    'graduation_term_id' => $fromTerm->id,
                    'grade_id' => $klass->grade_id,
                    'type' => $klass->type,
                    'graduation_year' => $toTerm->year,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
    
                $mapping[$klass->id] = $finalKlassId;
    
                Log::info('Klass rolled over to finals module', [
                    'originalKlassId' => $klass->id,
                    'finalKlassId' => $finalKlassId,
                    'klassName' => $klass->name,
                    'gradeId' => $klass->grade_id,
                    'isActive' => $klass->active,
                    'fromTermId' => $fromTerm->id,
                    'toTermId' => $toTerm->id
                ]);
    
            } catch (\Exception $e) {
                Log::error('Failed to rollover klass to finals module', [
                    'klassId' => $klass->id,
                    'klassName' => $klass->name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw new RolloverException("Failed to rollover klass '{$klass->name}' to finals module: " . $e->getMessage());
            }
        }
    
        Log::info('Completed klasses rollover to finals module', [
            'totalRolledOver' => count($mapping),
            'mapping' => $mapping,
            'fromTermId' => $fromTerm->id,
            'toTermId' => $toTerm->id
        ]);
    
        return $mapping;
    }

    protected function rolloverGradeSubjects($fromTerm, $toTerm, $graduatingGradeIds): array{
        $mapping = [];
        $gradeSubjects = GradeSubject::where('term_id', $fromTerm->id)->where('year', $fromTerm->year)->whereIn('grade_id', $graduatingGradeIds)->get();
        foreach ($gradeSubjects as $gradeSubject) {
            $finalGradeSubjectId = DB::table('final_grade_subjects')->insertGetId([
                'original_grade_subject_id' => $gradeSubject->id,
                'grade_id' => $gradeSubject->grade_id,
                'subject_id' => $gradeSubject->subject_id,
                'graduation_term_id' => $fromTerm->id,
                'department_id' => $gradeSubject->department_id,
                'graduation_year' => $toTerm->year,
                'type' => $gradeSubject->type,
                'mandatory' => $gradeSubject->mandatory,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $mapping[$gradeSubject->id] = $finalGradeSubjectId;
        }
        return $mapping;
    }

    protected function rolloverKlassSubjects($fromTerm, $toTerm, $finalKlassMapping, $finalGradeSubjectMapping): void{
        $klassSubjects = DB::table('klass_subject')
                          ->where('term_id', $fromTerm->id)
                          ->where('year', $fromTerm->year)
                          ->whereIn('klass_id', array_keys($finalKlassMapping))
                          ->get();

        foreach ($klassSubjects as $klassSubject) {
            $finalKlassId = $finalKlassMapping[$klassSubject->klass_id] ?? null;
            $finalGradeSubjectId = $finalGradeSubjectMapping[$klassSubject->grade_subject_id] ?? null;

            if ($finalKlassId && $finalGradeSubjectId) {
                DB::table('final_klass_subjects')->insert([
                    'original_klass_subject_id' => $klassSubject->id,
                    'final_klass_id' => $finalKlassId,
                    'final_grade_subject_id' => $finalGradeSubjectId,
                    'user_id' => $klassSubject->user_id,
                    'graduation_term_id' => $fromTerm->id,
                    'grade_id' => $klassSubject->grade_id,
                    'venue_id' => $klassSubject->venue_id,
                    'graduation_year' => $toTerm->year,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    protected function rolloverOptionalSubjects($fromTerm, $toTerm, $graduatingGradeIds, $finalGradeSubjectMapping): array{
        $mapping = [];
        $optionalSubjects = OptionalSubject::where('term_id', $fromTerm->id)
                                          ->whereIn('grade_id', $graduatingGradeIds)
                                          ->get();

        foreach ($optionalSubjects as $optionalSubject) {
            $finalGradeSubjectId = $finalGradeSubjectMapping[$optionalSubject->grade_subject_id] ?? null;

            if ($finalGradeSubjectId) {
                $finalOptionalSubjectId = DB::table('final_optional_subjects')->insertGetId([
                    'original_optional_subject_id' => $optionalSubject->id,
                    'name' => $optionalSubject->name,
                    'final_grade_subject_id' => $finalGradeSubjectId,
                    'user_id' => $optionalSubject->user_id,
                    'graduation_term_id' => $fromTerm->id,
                    'grade_id' => $optionalSubject->grade_id,
                    'grouping' => $optionalSubject->grouping,
                    'venue_id' => $optionalSubject->venue_id,
                    'active' => true,
                    'graduation_year' => $toTerm->year,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $mapping[$optionalSubject->id] = $finalOptionalSubjectId;
            }
        }

        return $mapping;
    }

    protected function rolloverHouses($fromTerm, $toTerm): array{
        $mapping = [];
        
        $houses = House::where('term_id', $fromTerm->id)
                      ->where('year', $fromTerm->year)
                      ->get();

        Log::info('Rollover houses for finals module', [
            'fromTermId' => $fromTerm->id,
            'toTermId' => $toTerm->id,
            'housesCount' => $houses->count()
        ]);

        foreach ($houses as $house) {
            $finalHouseId = DB::table('final_houses')->insertGetId([
                'original_house_id' => $house->id,
                'name' => $house->name,
                'color_code' => $house->color_code,
                'head' => $house->head,
                'assistant' => $house->assistant,
                'graduation_term_id' => $fromTerm->id,
                'graduation_year' => $toTerm->year,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $mapping[$house->id] = $finalHouseId;

            Log::info('House rolled over to finals module', [
                'originalHouseId' => $house->id,
                'finalHouseId' => $finalHouseId,
                'houseName' => $house->name,
                'fromTermId' => $fromTerm->id,
                'toTermId' => $toTerm->id
            ]);
        }

        return $mapping;
    }

    protected function rolloverStudents($f3Students, $fromTerm, $toTerm): array{
        $mapping = [];
        foreach ($f3Students as $student) {
            $studentTerm = $student->studentTerms()
                                  ->where('term_id', $fromTerm->id)
                                  ->where('status', 'Current')
                                  ->first();

            if ($studentTerm) {
                $finalStudentId = DB::table('final_students')->insertGetId([
                    'original_student_id' => $student->id,
                    'connect_id' => $student->connect_id,
                    'sponsor_id' => $student->sponsor_id,
                    'photo_path' => $student->photo_path,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'exam_number' => $student->exam_number,
                    'gender' => $student->gender,
                    'date_of_birth' => $student->date_of_birth,
                    'email' => $student->email,
                    'nationality' => $student->nationality,
                    'id_number' => $student->id_number,
                    'status' => 'Alumni',
                    'credit' => $student->credit,
                    'parent_is_staff' => $student->parent_is_staff,
                    'student_filter_id' => $student->student_filter_id,
                    'student_type_id' => $student->student_type_id,
                    'graduation_term_id' => $fromTerm->id,
                    'graduation_year' => $toTerm->year,
                    'graduation_grade_id' => $studentTerm->grade_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $mapping[$student->id] = $finalStudentId;
            }
        }

        return $mapping;
    }

    protected function rolloverStudentKlassRelationships($f3Students, $fromTerm, $toTerm, $finalStudentMapping, $finalKlassMapping): void{
        foreach ($f3Students as $student) {
            $finalStudentId = $finalStudentMapping[$student->id] ?? null;
            if (!$finalStudentId) continue;
            
            $currentClass = $student->classes()
                                   ->wherePivot('term_id', $fromTerm->id)
                                   ->first();

            if ($currentClass) {
                $finalKlassId = $finalKlassMapping[$currentClass->id] ?? null;
                if ($finalKlassId) {
                    DB::table('final_student_klass')->insert([
                        'final_student_id' => $finalStudentId,
                        'final_klass_id' => $finalKlassId,
                        'graduation_term_id' => $fromTerm->id,
                        'graduation_year' => $toTerm->year,
                        'grade_id' => $currentClass->grade_id,
                        'active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    protected function rolloverStudentOptionalSubjectRelationships($f3Students, $fromTerm, $toTerm, $finalStudentMapping, $finalOptionalSubjectMapping, $finalKlassMapping): void{
        foreach ($f3Students as $student) {
            $finalStudentId = $finalStudentMapping[$student->id] ?? null;
            if (!$finalStudentId) continue;

            $studentOptionalSubjects = DB::table('student_optional_subjects')
                                        ->where('student_id', $student->id)
                                        ->where('term_id', $fromTerm->id)
                                        ->get();

            foreach ($studentOptionalSubjects as $studentOptionalSubject) {
                $finalOptionalSubjectId = $finalOptionalSubjectMapping[$studentOptionalSubject->optional_subject_id] ?? null;
                $finalKlassId = $finalKlassMapping[$studentOptionalSubject->klass_id] ?? null;

                if ($finalOptionalSubjectId && $finalKlassId) {
                    DB::table('final_student_optional_subjects')->insert([
                        'final_student_id' => $finalStudentId,
                        'final_optional_subject_id' => $finalOptionalSubjectId,
                        'graduation_term_id' => $fromTerm->id,
                        'final_klass_id' => $finalKlassId,
                        'graduation_year' => $toTerm->year,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    protected function rolloverStudentHouseRelationships($f3Students, $fromTerm, $toTerm, $finalStudentMapping, $finalHouseMapping): void{
        foreach ($f3Students as $student) {
            $finalStudentId = $finalStudentMapping[$student->id] ?? null;
            if (!$finalStudentId) continue;

            $studentHouse = DB::table('student_house')
                             ->where('student_id', $student->id)
                             ->where('term_id', $fromTerm->id)
                             ->first();

            if ($studentHouse) {
                $finalHouseId = $finalHouseMapping[$studentHouse->house_id] ?? null;
                if ($finalHouseId) {
                    DB::table('final_student_houses')->insert([
                        'final_student_id' => $finalStudentId,
                        'final_house_id' => $finalHouseId,
                        'graduation_term_id' => $fromTerm->id,
                        'graduation_year' => $toTerm->year,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    Log::info('Student house relationship rolled over to finals module', [
                        'originalStudentId' => $student->id,
                        'finalStudentId' => $finalStudentId,
                        'originalHouseId' => $studentHouse->house_id,
                        'finalHouseId' => $finalHouseId,
                        'fromTermId' => $fromTerm->id,
                        'toTermId' => $toTerm->id
                    ]);
                }
            }
        }
    }
}

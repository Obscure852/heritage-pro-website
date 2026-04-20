<?php
namespace App\Helpers;
use App\Models\Student;
use App\Models\GradeSubject;
use App\Models\KlassSubject;
use App\Models\ScoreComment;
use App\Models\StudentTest;
use App\Models\Subject;
use App\Models\Test;
use Cache;
use Illuminate\Support\Facades\DB;
use Log;

Class AssessmentHelper{
    
    public static function calculateSubjectScoresAnalysis(Student $student, GradeSubject $subject, $selectedTermId, $grade) {
        $test = $student->tests()
                   ->where('term_id', $selectedTermId)
                   ->where('grade_subject_id', $subject->id)
                   ->where('type', 'Exam')
                   ->where('grade_id', $grade)
                   ->orderBy('sequence', 'desc')
                   ->first();
        
        if (!$test) {
            $subjectName = $subject->subject->name;
            $testByName = DB::table('student_tests as st')
                    ->join('tests as t', 'st.test_id', '=', 't.id')
                    ->join('grade_subject as gs', 't.grade_subject_id', '=', 'gs.id')
                    ->join('subjects as s', 'gs.subject_id', '=', 's.id')
                    ->where('st.student_id', $student->id)
                    ->where('s.name', $subjectName)
                    ->where('t.term_id', $selectedTermId)
                    ->where('t.type', 'Exam')
                    ->where('st.deleted_at', null)
                    ->where('t.deleted_at', null)
                    ->select('st.*', 't.id as test_id', 't.grade_subject_id')
                    ->first();
            
            if ($testByName) {
                return [
                    'subject' => $subjectName,
                    'score' => $testByName->score,
                    'percentage' => $testByName->percentage,
                    'points' => $testByName->points,
                    'grade' => $testByName->grade,
                ];
            }
        }
        
        $score = $test ? $test->pivot->score : null;
        $percentage = $test ? $test->pivot->percentage : null;
        $points = $test ? $test->pivot->points : null;
        $grade_result = $test ? $test->pivot->grade : null;
    
        return [
            'subject' => $subject->subject->name,
            'score' => $score,
            'percentage' => $percentage,
            'points' => $points,
            'grade' => $grade_result,
        ];
    }

    public static function calculateSubjectCAScoresAnalysis(Student $student, GradeSubject $subject, $selectedTermId,$sequence,$grade) {
        $test = $student->tests()
                        ->where('term_id', $selectedTermId)
                        ->where('grade_subject_id', $subject->id)
                        ->where('sequence',$sequence)
                        ->where('type', 'CA')
                        ->where('grade_id', $grade)
                        ->orderBy('sequence', 'asc')
                        ->first();
        if (!$test) {
            $subjectName = $subject->subject->name;
            
            $testByName = DB::table('student_tests as st')
                    ->join('tests as t', 'st.test_id', '=', 't.id')
                    ->join('grade_subject as gs', 't.grade_subject_id', '=', 'gs.id')
                    ->join('subjects as s', 'gs.subject_id', '=', 's.id')
                    ->where('st.student_id', $student->id)
                    ->where('s.name', $subjectName)
                    ->where('t.term_id', $selectedTermId)
                    ->where('t.type', 'Exam')
                    ->where('st.deleted_at', null)
                    ->where('t.deleted_at', null)
                    ->select('st.*', 't.id as test_id', 't.grade_subject_id')
                    ->first();
            
            if ($testByName) {
                return [
                    'subject' => $subjectName,
                    'score' => $testByName->score,
                    'percentage' => $testByName->percentage,
                    'points' => $testByName->points,
                    'grade' => $testByName->grade,
                ];
            }
        }
        

        $score = $test ? $test->pivot->score : null;
        $percentage = $test ? $test->pivot->percentage : null;
        $points = $test ? $test->pivot->points : null;
        $grade = $test ? $test->pivot->grade : null;

        return [
            'subject' => $subject->subject->name,
            'score' => $score,
            'percentage' => $percentage,
            'points' => $points,
            'grade' => $grade,
        ];
    }


    public static function calculateSubjectGeneralScoresAnalysis(Student $student, GradeSubject $subject, $selectedTermId, $type, $sequence, $grade) {
        $test = $student->tests()
                        ->where('term_id', $selectedTermId)
                        ->where('grade_subject_id', $subject->id)
                        ->where('sequence',$sequence)
                        ->where('type', $type)
                        ->where('grade_id', $grade)
                        ->orderBy('sequence', 'asc')
                        ->first();
        
        if (!$test) {
            $subjectName = $subject->subject->name;
            $testByName = DB::table('student_tests as st')
                    ->join('tests as t', 'st.test_id', '=', 't.id')
                    ->join('grade_subject as gs', 't.grade_subject_id', '=', 'gs.id')
                    ->join('subjects as s', 'gs.subject_id', '=', 's.id')
                    ->where('st.student_id', $student->id)
                    ->where('s.name', $subjectName)
                    ->where('t.term_id', $selectedTermId)
                    ->where('t.type', $type)
                    ->where('st.deleted_at', null)
                    ->where('t.deleted_at', null)
                    ->select('st.*', 't.id as test_id', 't.grade_subject_id')
                    ->first();
            
            if ($testByName) {
                return [
                    'subject' => $subjectName,
                    'score' => $testByName->score,
                    'percentage' => $testByName->percentage,
                    'points' => $testByName->points,
                    'grade' => $testByName->grade,
                ];
            }
        }

        $score = $test ? $test->pivot->score : null;
        $percentage = $test ? $test->pivot->percentage : null;
        $points = $test ? $test->pivot->points : null;
        $grade = $test ? $test->pivot->grade : null;

        return [
            'subject' => $subject->subject->name,
            'score' => $score,
            'percentage' => $percentage,
            'points' => $points,
            'grade' => $grade,
        ];
    }


    public static function getRandomCommentForScore($score){
        $comments = ScoreComment::where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->get();

        return $comments->isNotEmpty() 
            ? $comments->random()->comment 
            : null;
    }

    public static function calculatePoints($student, $subjects, $selectedTermId, $isForeigner){
        $mandatoryPoints = 0;
        $optionalPoints = [];
        $corePoints = [];
    
        foreach ($subjects as $subject) {
            $points = self::getSubjectPoints($student, $subject, $selectedTermId);
            
            if ($subject->subject->name == "Setswana") {
                if (!$isForeigner) {
                    $mandatoryPoints += $points;
                    continue;
                }

                if (!$subject->type) {
                    $optionalPoints[] = $points;
                    continue;
                }

                $corePoints[] = $points;
                continue;
            }
    
            if ($subject->mandatory) {
                $mandatoryPoints += $points;
            } elseif (!$subject->mandatory && !$subject->type) {
                $optionalPoints[] = $points;
            } elseif (!$subject->mandatory && $subject->type) {
                $corePoints[] = $points;
            }
        }
    
        rsort($optionalPoints);
        rsort($corePoints);
    
        if ($isForeigner) {
            $bestOptionalPoints = array_sum(array_slice($optionalPoints, 0, 2));
            $remainingOptionals = array_slice($optionalPoints, 2);
        } else {
            $bestOptionalPoints = count($optionalPoints) ? $optionalPoints[0] : 0;
            $remainingOptionals = array_slice($optionalPoints, 1);
        }
    
        $combinedRemaining = array_merge($remainingOptionals, $corePoints);
        rsort($combinedRemaining);
        $bestFromCombined = array_sum(array_slice($combinedRemaining, 0, 2));
        return [$mandatoryPoints, $bestOptionalPoints, $bestFromCombined];
    }

    public static function calculatePointsGeneral($student,$isForeigner,$subjects,$selectedTermId,$type,$sequence){
        $mandatoryPoints = 0;
        $optionalPoints = [];
        $corePoints = [];
    
        foreach ($subjects as $subject) {
            $points = self::getSubjectPointsGeneral($student,$subject,$selectedTermId,$type,$sequence);
            if ($subject->subject->name == "Setswana") {
                if (!$isForeigner) {
                    $mandatoryPoints += $points;
                    continue; 
                }

                if (!$subject->type) {
                    $optionalPoints[] = $points;
                    continue;
                }

                $corePoints[] = $points;
                continue;
            }
    
            if ($subject->mandatory) {
                $mandatoryPoints += $points;
            } elseif (!$subject->mandatory && !$subject->type) {
                $optionalPoints[] = $points;
            } elseif (!$subject->mandatory && $subject->type) {
                $corePoints[] = $points;
            }
        }
    
        rsort($optionalPoints);
        rsort($corePoints);
    
        if ($isForeigner) {
            $bestOptionalPoints = array_sum(array_slice($optionalPoints, 0, 2));
            $remainingOptionals = array_slice($optionalPoints, 2);
        } else {
            $bestOptionalPoints = count($optionalPoints) ? $optionalPoints[0] : 0;
            $remainingOptionals = array_slice($optionalPoints, 1);
        }
    
        $combinedRemaining = array_merge($remainingOptionals, $corePoints);
        rsort($combinedRemaining);
        $bestFromCombined = array_sum(array_slice($combinedRemaining, 0, 2));
        return [$mandatoryPoints, $bestOptionalPoints, $bestFromCombined];
    }

    public static function calculatePointsCA($student, $subjects, $selectedTermId, $isForeigner,$sequence) {
        $mandatoryPoints = 0;
        $optionalPointsArray = [];
        $corePointsArray = [];
        $mandatorySubjectsDetails = [];
        $optionalSubjectsDetails = [];
        $coreSubjectsDetails = [];
    
        foreach ($subjects as $subject) {
            $points = self::getSubjectPointsCA($student, $subject, $selectedTermId,$sequence);
            $subjectDetail = [
                'name' => $subject->subject->name,
                'points' => $points
            ];
            
            if ($subject->subject->name == "Setswana") {
                if (!$isForeigner) {
                    $mandatoryPoints += $points;
                    $mandatorySubjectsDetails[] = $subjectDetail;
                    continue; 
                }

                if (!$subject->type) {
                    $optionalPointsArray[] = $points;
                    $optionalSubjectsDetails[] = $subjectDetail;
                    continue;
                }

                $corePointsArray[] = $points;
                $coreSubjectsDetails[] = $subjectDetail;
                continue;
            }
    
            if ($subject->mandatory) {
                $mandatoryPoints += $points;
                $mandatorySubjectsDetails[] = $subjectDetail;
            } elseif (!$subject->mandatory && !$subject->type) {
                $optionalPointsArray[] = $points;
                $optionalSubjectsDetails[] = $subjectDetail;
            } elseif (!$subject->mandatory && $subject->type) {
                $corePointsArray[] = $points;
                $coreSubjectsDetails[] = $subjectDetail;
            }
        }
        
        rsort($optionalPointsArray);
        rsort($corePointsArray);
    
        if ($isForeigner) {
            $bestOptionalPoints = array_sum(array_slice($optionalPointsArray, 0, 2));
            $remainingOptionals = array_slice($optionalPointsArray, 2);
        } else {
            $bestOptionalPoints = count($optionalPointsArray) ? $optionalPointsArray[0] : 0;
            $remainingOptionals = array_slice($optionalPointsArray, 1);
        }
    
        $combinedRemaining = array_merge($remainingOptionals, $corePointsArray);
        rsort($combinedRemaining);
        $bestFromCombined = array_sum(array_slice($combinedRemaining, 0, 2));
        
        return [
            $mandatoryPoints, 
            $bestOptionalPoints, 
            $bestFromCombined, 
            $mandatorySubjectsDetails,
            $optionalSubjectsDetails,
            $coreSubjectsDetails
        ];
    }

    public static function getSubjectPoints($student,$subject,$selectedTermId){
        $examTest = $student->tests
                ->where('term_id', $selectedTermId)
                ->where('grade_subject_id', $subject->id)
                ->where('type', 'Exam')
                ->first();

        if(!empty($examTest)){
            return $examTest->pivot->points;
        }
        return 0;
    }

    public static function getSubjectPointsCA($student,$subject,$selectedTermId,$sequence){
        $examTest = $student->tests
                ->where('term_id', $selectedTermId)
                ->where('grade_subject_id', $subject->id)
                ->where('type', 'CA')
                ->where('sequence', $sequence)
                ->first();

        if(!empty($examTest)){
            return $examTest->pivot->points;
        }
        return 0;
    }

    public static function getSubjectPointsGeneral($student,$subject,$selectedTermId,$type,$sequence){
        $examTest = $student->tests
                ->where('term_id', $selectedTermId)
                ->where('grade_subject_id', $subject->id)
                ->where('type', $type)
                ->where('sequence', $sequence)
                ->first();

        if(!empty($examTest)){
            return $examTest->pivot->points;
        }
        return 0;
    }

    public static function determineGrade($totalPoints, $currentClass){
        return DB::table('overall_points_matrix')
            ->where('min', '<=', $totalPoints)
            ->where('max', '>=', $totalPoints)
            ->where('academic_year', $currentClass->grade->name)
            ->value('grade');
    }

    public static function formatPercentage($count, $total){
        return $total > 0 ? number_format(($count / $total) * 100, 1) . '%' : '0%';
    }

    public static function purgeSoftDeletedKlassSubjects($dryRun = true, $specificGradeId = null, $specificTermId = null, $olderThanDays = null){
        $startTime = microtime(true);
        $result = [
            'execution_mode' => $dryRun ? 'DRY RUN' : 'LIVE EXECUTION',
            'soft_deleted_found' => [],
            'statistics' => [
                'klass_subjects_found' => 0,
                'klass_subjects_purged' => 0
            ],
            'execution_time' => 0
        ];

        try {
            Log::info("=== PURGING SOFT-DELETED KLASS SUBJECTS ===", [
                'mode' => $result['execution_mode'],
                'grade_filter' => $specificGradeId,
                'term_filter' => $specificTermId,
                'older_than_days' => $olderThanDays
            ]);

            Log::info("NOTE: This will only delete klass_subject records. Tests and student data will remain untouched.");
            $query = "
                SELECT 
                    ks.id as klass_subject_id,
                    k.name as class_name,
                    s.name as subject_name,
                    g.name as grade_name,
                    CONCAT(t.term, ' ', t.year) as term_name,
                    CONCAT(COALESCE(u.firstname, ''), ' ', COALESCE(u.lastname, '')) as teacher_name,
                    v.name as venue_name,
                    ks.deleted_at,
                    DATEDIFF(NOW(), ks.deleted_at) as days_since_deletion
                FROM klass_subject ks
                INNER JOIN klasses k ON ks.klass_id = k.id
                INNER JOIN grade_subject gs ON ks.grade_subject_id = gs.id
                INNER JOIN subjects s ON gs.subject_id = s.id
                INNER JOIN grades g ON ks.grade_id = g.id
                INNER JOIN terms t ON ks.term_id = t.id
                LEFT JOIN users u ON ks.user_id = u.id
                LEFT JOIN venues v ON ks.venue_id = v.id
                WHERE ks.deleted_at IS NOT NULL
            ";

            $conditions = [];
            if ($specificGradeId) {
                $conditions[] = "ks.grade_id = " . (int)$specificGradeId;
            }

            if ($specificTermId) {
                $conditions[] = "ks.term_id = " . (int)$specificTermId;
            }

            if ($olderThanDays) {
                $conditions[] = "DATEDIFF(NOW(), ks.deleted_at) >= " . (int)$olderThanDays;
            }

            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }

            $query .= " ORDER BY ks.deleted_at ASC";

            $softDeletedKlassSubjects = DB::select($query);

            if (empty($softDeletedKlassSubjects)) {
                Log::info("No soft-deleted klass subjects found matching the criteria.");
                $result['execution_time'] = round(microtime(true) - $startTime, 2);
                return $result;
            }

            $klassSubjectIds = [];
            foreach ($softDeletedKlassSubjects as $klassSubject) {
                $klassSubjectIds[] = $klassSubject->klass_subject_id;

                $result['soft_deleted_found'][] = [
                    'id' => $klassSubject->klass_subject_id,
                    'class_name' => $klassSubject->class_name,
                    'subject_name' => $klassSubject->subject_name,
                    'grade' => $klassSubject->grade_name,
                    'term' => $klassSubject->term_name,
                    'teacher' => $klassSubject->teacher_name,
                    'venue' => $klassSubject->venue_name,
                    'deleted_at' => $klassSubject->deleted_at,
                    'days_ago' => $klassSubject->days_since_deletion
                ];
            }

            $result['statistics']['klass_subjects_found'] = count($softDeletedKlassSubjects);
            Log::info("=== SOFT-DELETED KLASS SUBJECTS FOUND ===");
            Log::info(sprintf("| %-4s | %-12s | %-15s | %-10s | %-12s | %-15s | %-15s | %-8s |", 
                "ID", "Class", "Subject", "Grade", "Term", "Teacher", "Venue", "Days Ago"));
            Log::info(str_repeat("-", 115));

            foreach ($result['soft_deleted_found'] as $ks) {
                Log::info(sprintf("| %-4s | %-12s | %-15s | %-10s | %-12s | %-15s | %-15s | %-8s |", 
                    $ks['id'],
                    substr($ks['class_name'], 0, 12),
                    substr($ks['subject_name'], 0, 15),
                    substr($ks['grade'], 0, 10),
                    substr($ks['term'], 0, 12),
                    substr($ks['teacher'], 0, 15),
                    substr($ks['venue'] ?? 'N/A', 0, 15),
                    $ks['days_ago']
                ));
            }
            Log::info(str_repeat("-", 115));
            if (!$dryRun) {
                DB::beginTransaction();

                try {
                    if (!empty($klassSubjectIds)) {
                        $klassSubjectsPurged = DB::table('klass_subject')
                            ->whereIn('id', $klassSubjectIds)
                            ->delete();
                        $result['statistics']['klass_subjects_purged'] = $klassSubjectsPurged;
                        
                        Log::info("PURGED {$klassSubjectsPurged} klass subjects");
                    }

                    DB::commit();
                    Log::info("All soft-deleted klass subjects purged successfully");
                    Cache::flush();

                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            } else {
                Log::info("DRY RUN - Would purge " . count($result['soft_deleted_found']) . " klass subjects");
            }

        } catch (\Exception $e) {
            Log::error("Error during soft-deleted klass subjects purge: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        $result['execution_time'] = round(microtime(true) - $startTime, 2);

        Log::info("=== KLASS SUBJECTS PURGE SUMMARY ===");
        Log::info("Mode: " . $result['execution_mode']);
        Log::info("Klass Subjects Found: " . $result['statistics']['klass_subjects_found']);
        Log::info("Klass Subjects Purged: " . $result['statistics']['klass_subjects_purged']);
        Log::info("Execution Time: " . $result['execution_time'] . " seconds");
        Log::info("NOTE: Tests and student data were preserved");
        
        return $result;
    }


    public static function purgeSoftDeletedOptionalSubjects($dryRun = true, $specificGradeId = null, $specificTermId = null, $olderThanDays = null){
        $startTime = microtime(true);
        $result = [
            'execution_mode' => $dryRun ? 'DRY RUN' : 'LIVE EXECUTION',
            'soft_deleted_found' => [],
            'statistics' => [
                'optional_subjects_found' => 0,
                'optional_subjects_purged' => 0,
                'student_enrollments_purged' => 0,
                'affected_students' => 0
            ],
            'execution_time' => 0
        ];

        try {
            Log::info("=== PURGING SOFT-DELETED OPTIONAL SUBJECTS ===", [
                'mode' => $result['execution_mode'],
                'grade_filter' => $specificGradeId,
                'term_filter' => $specificTermId,
                'older_than_days' => $olderThanDays
            ]);

            Log::info("NOTE: This will only delete optional subjects and student enrollments. Tests and student test data will remain untouched.");

            $query = "
                SELECT 
                    os.id as optional_subject_id,
                    os.name as optional_subject_name,
                    g.name as grade_name,
                    CONCAT(t.term, ' ', t.year) as term_name,
                    s.name as subject_name,
                    CONCAT(COALESCE(u.firstname, ''), ' ', COALESCE(u.lastname, '')) as teacher_name,
                    v.name as venue_name,
                    os.grouping,
                    os.deleted_at,
                    DATEDIFF(NOW(), os.deleted_at) as days_since_deletion,
                    (SELECT COUNT(*) FROM student_optional_subjects sos 
                    WHERE sos.optional_subject_id = os.id) as enrollment_count
                FROM optional_subjects os
                INNER JOIN grade_subject gs ON os.grade_subject_id = gs.id
                INNER JOIN subjects s ON gs.subject_id = s.id
                INNER JOIN grades g ON os.grade_id = g.id
                INNER JOIN terms t ON os.term_id = t.id
                LEFT JOIN users u ON os.user_id = u.id
                LEFT JOIN venues v ON os.venue_id = v.id
                WHERE os.deleted_at IS NOT NULL
            ";

            $conditions = [];
            if ($specificGradeId) {
                $conditions[] = "os.grade_id = " . (int)$specificGradeId;
            }

            if ($specificTermId) {
                $conditions[] = "os.term_id = " . (int)$specificTermId;
            }

            if ($olderThanDays) {
                $conditions[] = "DATEDIFF(NOW(), os.deleted_at) >= " . (int)$olderThanDays;
            }

            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }

            $query .= " ORDER BY os.deleted_at ASC";

            $softDeletedOptionalSubjects = DB::select($query);

            if (empty($softDeletedOptionalSubjects)) {
                Log::info("No soft-deleted optional subjects found matching the criteria.");
                $result['execution_time'] = round(microtime(true) - $startTime, 2);
                return $result;
            }

            $optionalSubjectIds = [];
            foreach ($softDeletedOptionalSubjects as $optionalSubject) {
                $optionalSubjectIds[] = $optionalSubject->optional_subject_id;

                $result['soft_deleted_found'][] = [
                    'id' => $optionalSubject->optional_subject_id,
                    'name' => $optionalSubject->optional_subject_name,
                    'subject_name' => $optionalSubject->subject_name,
                    'grade' => $optionalSubject->grade_name,
                    'term' => $optionalSubject->term_name,
                    'teacher' => $optionalSubject->teacher_name,
                    'venue' => $optionalSubject->venue_name,
                    'grouping' => $optionalSubject->grouping,
                    'deleted_at' => $optionalSubject->deleted_at,
                    'days_ago' => $optionalSubject->days_since_deletion,
                    'enrollments' => $optionalSubject->enrollment_count
                ];
            }

            $result['statistics']['optional_subjects_found'] = count($softDeletedOptionalSubjects);

            Log::info("=== SOFT-DELETED OPTIONAL SUBJECTS FOUND ===");
            Log::info(sprintf("| %-4s | %-15s | %-15s | %-10s | %-12s | %-15s | %-12s | %-8s | %-6s |", 
                "ID", "Name", "Subject", "Grade", "Term", "Teacher", "Grouping", "Days Ago", "Enroll"));
            Log::info(str_repeat("-", 125));

            foreach ($result['soft_deleted_found'] as $os) {
                Log::info(sprintf("| %-4s | %-15s | %-15s | %-10s | %-12s | %-15s | %-12s | %-8s | %-6s |", 
                    $os['id'],
                    substr($os['name'], 0, 15),
                    substr($os['subject_name'], 0, 15),
                    substr($os['grade'], 0, 10),
                    substr($os['term'], 0, 12),
                    substr($os['teacher'], 0, 15),
                    substr($os['grouping'] ?? 'N/A', 0, 12),
                    $os['days_ago'],
                    $os['enrollments']
                ));
            }
            Log::info(str_repeat("-", 125));
            if (!$dryRun) {
                DB::beginTransaction();

                try {
                    if (!empty($optionalSubjectIds)) {
                        $affectedStudents = DB::table('student_optional_subjects')
                            ->whereIn('optional_subject_id', $optionalSubjectIds)
                            ->distinct('student_id')
                            ->count('student_id');
                        $result['statistics']['affected_students'] = $affectedStudents;
                    }

                    if (!empty($optionalSubjectIds)) {
                        $enrollmentsPurged = DB::table('student_optional_subjects')
                            ->whereIn('optional_subject_id', $optionalSubjectIds)
                            ->delete();
                        $result['statistics']['student_enrollments_purged'] = $enrollmentsPurged;
                        
                        if ($enrollmentsPurged > 0) {
                            Log::info("Purged {$enrollmentsPurged} student enrollments");
                        }
                    }

                    if (!empty($optionalSubjectIds)) {
                        $optionalSubjectsPurged = DB::table('optional_subjects')->whereIn('id', $optionalSubjectIds)->delete();
                        $result['statistics']['optional_subjects_purged'] = $optionalSubjectsPurged;
                        
                        Log::info("PURGED {$optionalSubjectsPurged} optional subjects");
                    }

                    DB::commit();
                    Log::info("All soft-deleted optional subjects and enrollments purged successfully");
                    Cache::flush();

                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            } else {
                $totalEnrollments = array_sum(array_column($result['soft_deleted_found'], 'enrollments'));
                Log::info("DRY RUN - Would purge " . count($result['soft_deleted_found']) . 
                        " optional subjects and {$totalEnrollments} student enrollments");
            }

        } catch (\Exception $e) {
            Log::error("Error during soft-deleted optional subjects purge: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        $result['execution_time'] = round(microtime(true) - $startTime, 2);
        
        Log::info("=== OPTIONAL SUBJECTS PURGE SUMMARY ===");
        Log::info("Mode: " . $result['execution_mode']);
        Log::info("Optional Subjects Found: " . $result['statistics']['optional_subjects_found']);
        Log::info("Optional Subjects Purged: " . $result['statistics']['optional_subjects_purged']);
        Log::info("Student Enrollments Purged: " . $result['statistics']['student_enrollments_purged']);
        Log::info("Affected Students: " . $result['statistics']['affected_students']);
        Log::info("Execution Time: " . $result['execution_time'] . " seconds");
        Log::info("NOTE: Tests and student test data were preserved");
        
        return $result;
    }

}

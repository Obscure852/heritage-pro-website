<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentController;
use App\Exports\ClassCreditsSummaryExport;
use App\Exports\ClassListAnalysisExport;
use App\Exports\ClassPerformanceAnalysisExport;
use App\Exports\ClassSubjectAnalysisExport;
use App\Exports\DepartmentPerformanceExport;
use Exception;
use Illuminate\Validation\ValidationException;
use App\Exports\CAAnalysisExport;
use App\Exports\ClassAnalysisExport;
use App\Exports\ClassCreditsPerformanceExport;
use App\Exports\ClassPerformanceExport;
use App\Exports\DepartmentAnalysisExport;
use App\Exports\GradeAnalysisExport;
use App\Exports\GradeDistributionExport;
use App\Exports\GradeExamAnalysisExport;
use App\Exports\GradePerformanceAnalysisExport;
use App\Exports\GradePerformanceStreamExport;
use App\Exports\OverallTeacherPerformanceExport;
use Illuminate\Support\Str;
use App\Exports\GradeSubjectAnalysisExport;
use App\Exports\GradeValueAnalysisExport;
use App\Exports\HouseAnalysisExport;
use App\Exports\HouseCreditsPerformanceExport;
use App\Exports\RegionalGradePerformanceReportExport;
use App\Exports\SeniorSubjectABCPerformanceExport;
use App\Exports\SubjectGradePerformanceReportExport;
use App\Exports\SubjectTeacherGradeAnalysisExport;
use App\Exports\SubjectAnalysisExport;
use App\Exports\SubjectGradeDistributionExport;
use App\Exports\TeacherPerformanceExport;
use App\Exports\TeacherPerformanceSeniorExport;
use App\Exports\TestComparisonAnalysisExport;
use App\Exports\ValueAdditionAnalysisExport;
use App\Helpers\AssessmentHelper;
use App\Helpers\CacheHelper;
use App\Helpers\TermHelper;
use App\Jobs\RecalculateGrades;
use App\Models\SchoolSetup;
use App\Jobs\SendBulkReportCards;
use App\Models\CommentBank;
use App\Models\Comment;
use App\Models\CriteriaBasedStudentTest;
use App\Models\Email;
use App\Models\Klass;
use App\Models\Student;
use App\Models\StudentTest;
use App\Models\Test;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Term;
use App\Models\KlassSubject;
use App\Models\GradingScale;
use App\Models\OverallGradingMatrix;
use App\Models\SubjectComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use App\Models\Holiday;
use App\Models\OptionalSubject;
use App\Models\House;
use App\Models\PSLE;
use App\Models\ScoreComment;
use App\Models\Venue;
use Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Base Assessment Controller
 *
 * Contains shared functionality used by all school-type specific assessment controllers.
 * Primary, Junior, and Senior controllers extend this class.
 */
class BaseAssessmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get the current school type from SchoolSetup
     */
    protected function getSchoolType(): string
    {
        return SchoolSetup::schoolType() ?? SchoolSetup::TYPE_JUNIOR;
    }

    /**
     * Get the current term
     */
    protected function getCurrentTerm()
    {
        return TermHelper::getCurrentTerm();
    }

    /**
     * Get all terms
     */
    protected function getTerms()
    {
        return StudentController::terms();
    }

    /**
     * Common method to get grading scale
     */
    protected function getGradingScale($termId = null)
    {
        return GradingScale::when($termId, function ($query) use ($termId) {
            return $query->where('term_id', $termId);
        })->orderBy('min_score', 'desc')->get();
    }

    /**
     * Get grade from percentage based on grading scale
     */
    protected function getGrade($percentage, $gradingScale = null)
    {
        if (!$gradingScale) {
            $gradingScale = $this->getGradingScale();
        }

        foreach ($gradingScale as $scale) {
            if ($percentage >= $scale->min_score && $percentage <= $scale->max_score) {
                return $scale->grade;
            }
        }

        return 'U'; // Ungraded
    }

    /**
     * Calculate class average for a given set of scores
     */
    protected function calculateClassAverage($scores)
    {
        if (empty($scores)) {
            return 0;
        }

        return round(array_sum($scores) / count($scores), 2);
    }

    /**
     * Common PDF generation settings
     */
    protected function getPdfConfig()
    {
        return [
            'paper' => 'a4',
            'orientation' => 'portrait',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ];
    }

    /**
     * Get school setup data
     */
    protected function getSchoolSetup()
    {
        return SchoolSetup::first();
    }

    /**
     * Calculate class rankings based on total points (with average percentage tiebreaker)
     */
    protected function calculateClassRankings($students, $selectedTermId)
    {
        $rankings = [];
        foreach ($students as $student) {
            $subjects = $student->tests->pluck('subject')->unique();
            list($mandatoryPoints, $optionalPoints, $corePoints) = $this->calculatePoints($student, $subjects, $selectedTermId, $student->nationality !== 'Motswana', 'Exam');
            $totalPoints = $mandatoryPoints + $optionalPoints + $corePoints;
            $averagePercentage = $this->calculateAveragePercentage($student, $selectedTermId);
            $rankings[] = [
                'id' => $student->id,
                'totalPoints' => $totalPoints,
                'averagePercentage' => $averagePercentage
            ];
        }

        // Sort by totalPoints DESC, then averagePercentage DESC for tiebreaking
        usort($rankings, function ($a, $b) {
            if ($b['totalPoints'] !== $a['totalPoints']) {
                return $b['totalPoints'] <=> $a['totalPoints'];
            }
            return $b['averagePercentage'] <=> $a['averagePercentage'];
        });
        return $rankings;
    }

    /**
     * Calculate class average from rankings
     */
    protected function calculateClassAverageFromRankings($rankings)
    {
        $totalPoints = array_sum(array_column($rankings, 'totalPoints'));
        $numberOfStudents = count($rankings);
        return $numberOfStudents > 0 ? $totalPoints / $numberOfStudents : 0;
    }

    /**
     * Get student position from rankings (handles ties - same points & percentage share same position)
     */
    protected function getStudentPosition($rankings, $studentId)
    {
        $position = 1;
        $previousPoints = null;
        $previousPercentage = null;

        foreach ($rankings as $index => $ranking) {
            // Only increment position if this student has different points/percentage than previous
            if ($index > 0) {
                if ($ranking['totalPoints'] !== $previousPoints ||
                    $ranking['averagePercentage'] !== $previousPercentage) {
                    $position = $index + 1;
                }
            }

            if ($ranking['id'] == $studentId) {
                return $position;
            }

            $previousPoints = $ranking['totalPoints'];
            $previousPercentage = $ranking['averagePercentage'];
        }

        return 'N/A';
    }

    /**
     * Calculate points for a student based on subjects
     */
    protected function calculatePoints($student, $subjects, $selectedTermId, $isForeigner, $type, $sequence = 1)
    {
        $mandatoryPoints = 0;
        $optionalPoints = [];
        $corePoints = [];

        foreach ($subjects as $subject) {
            $points = $this->getSubjectPoints($student, $subject, $selectedTermId, $type, $sequence);

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

    /**
     * Get subject points for a student
     */
    protected function getSubjectPoints($student, $subject, $selectedTermId, $type = 'Exam', $sequence = 1)
    {
        $examTest = $student->tests
            ->where('term_id', $selectedTermId)
            ->where('grade_subject_id', $subject->id)
            ->where('type', $type)
            ->where('sequence', $sequence)
            ->first();

        if (!empty($examTest)) {
            return $examTest->pivot->points;
        }
        return 0;
    }

    /**
     * Determine overall grade based on total points
     */
    protected function determineGrade($totalPoints, $currentClass)
    {
        return DB::table('overall_points_matrix')
            ->where('min', '<=', $totalPoints)
            ->where('max', '>=', $totalPoints)
            ->where('academic_year', $currentClass->grade->name)
            ->value('grade');
    }

    /**
     * Get next term start date
     */
    public function getNextTermStartDate($currentTerm)
    {
        $nextTerm = Term::where('start_date', '>', $currentTerm->end_date)
            ->orderBy('start_date', 'asc')
            ->first();

        return $nextTerm ? $nextTerm->start_date : null;
    }

    /**
     * Calculate grade rankings
     */
    protected function calculateGradeRankings($gradeId, $selectedTermId)
    {
        $students = Student::whereHas('classes', function ($query) use ($gradeId, $selectedTermId) {
            $query->whereHas('grade', function ($query) use ($gradeId) {
                $query->where('grades.id', $gradeId);
            })->where('klass_student.term_id', $selectedTermId);
        })->with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)->where('type', 'Exam');
            }
        ])->get();

        $rankings = [];
        foreach ($students as $student) {
            $subjects = $student->tests->pluck('subject')->unique();
            $isForeigner = $student->nationality !== 'Motswana';

            list($mandatoryPoints, $optionalPoints, $corePoints) = $this->calculatePoints(
                $student,
                $subjects,
                $selectedTermId,
                $isForeigner,
                'Exam'
            );

            $totalPoints = $mandatoryPoints + $optionalPoints + $corePoints;
            $averagePercentage = $this->calculateAveragePercentage($student, $selectedTermId);

            $rankings[] = [
                'id' => $student->id,
                'totalPoints' => $totalPoints,
                'averagePercentage' => $averagePercentage,
                'student_name' => $student->getFullNameAttribute(),
                'class_name' => $student->currentClass() ? $student->currentClass()->name : 'N/A'
            ];
        }

        // Sort by totalPoints DESC, then averagePercentage DESC for tiebreaking
        usort($rankings, function ($a, $b) {
            if ($b['totalPoints'] !== $a['totalPoints']) {
                return $b['totalPoints'] <=> $a['totalPoints'];
            }
            return $b['averagePercentage'] <=> $a['averagePercentage'];
        });

        return $rankings;
    }

    /**
     * Get student position in grade (handles ties - same points & percentage share same position)
     */
    protected function getStudentGradePosition($gradeRankings, $studentId)
    {
        $position = 1;
        $previousPoints = null;
        $previousPercentage = null;

        foreach ($gradeRankings as $index => $ranking) {
            // Only increment position if this student has different points/percentage than previous
            if ($index > 0) {
                if ($ranking['totalPoints'] !== $previousPoints ||
                    $ranking['averagePercentage'] !== $previousPercentage) {
                    $position = $index + 1;
                }
            }

            if ($ranking['id'] == $studentId) {
                return $position;
            }

            $previousPoints = $ranking['totalPoints'];
            $previousPercentage = $ranking['averagePercentage'];
        }

        return null;
    }

    /**
     * Calculate grade average
     */
    protected function calculateGradeAverage($gradeRankings)
    {
        if (empty($gradeRankings)) {
            return 0;
        }

        $totalPoints = array_sum(array_column($gradeRankings, 'totalPoints'));
        return $totalPoints / count($gradeRankings);
    }

    /**
     * Calculate subject scores for a student
     */
    protected function calculateSubjectScores(Student $student, GradeSubject $subject, $selectedTermId)
    {
        $examTest = $student->tests()->where('term_id', $selectedTermId)
            ->where('grade_subject_id', $subject->id)
            ->where('type', 'Exam')
            ->first();

        $caTest = $student->tests()->where('term_id', $selectedTermId)
            ->where('grade_subject_id', $subject->id)
            ->where('type', 'CA')
            ->first();

        $subjectComment = $student->subjectComments()->where('grade_subject_id', $subject->id)
            ->where('term_id', $selectedTermId)
            ->first();

        $examScore = $examTest ? $examTest->pivot->score : null;
        $examPercentage = $examTest ? $examTest->pivot->percentage : null;
        $examPoints = $examTest ? $examTest->pivot->points : null;
        $examGrade = $examTest ? $examTest->pivot->grade : null;

        $caAverage = $caTest ? $caTest->pivot->avg_score : null;
        $caAverageGrade = $caTest ? $caTest->pivot->avg_grade : null;

        return [
            'subject' => $subject->subject->name,
            'caAverage' => $caAverage,
            'score' => $examScore,
            'percentage' => $examPercentage,
            'points' => $examPoints,
            'grade' => $examGrade,
            'comments' => $subjectComment ? $subjectComment->remarks : 'No comments',
            'caAverageGrade' => $caAverageGrade,
        ];
    }

    /**
     * Calculate average percentage of subjects that contribute to points (for tiebreaking)
     * Uses the same subject selection logic as calculatePoints()
     */
    protected function calculateAveragePercentage($student, $selectedTermId): float
    {
        $subjects = $student->tests->pluck('subject')->unique();
        $isForeigner = $student->nationality !== 'Motswana';

        $mandatoryPercentages = [];
        $optionalPercentages = [];
        $corePercentages = [];

        foreach ($subjects as $subject) {
            $test = $student->tests
                ->where('term_id', $selectedTermId)
                ->where('grade_subject_id', $subject->id)
                ->where('type', 'Exam')
                ->first();

            $percentage = $test ? ($test->pivot->percentage ?? 0) : 0;

            if ($subject->subject->name == "Setswana") {
                if (!$isForeigner) {
                    $mandatoryPercentages[] = $percentage;
                    continue;
                }

                if (!$subject->type) {
                    $optionalPercentages[] = $percentage;
                    continue;
                }

                $corePercentages[] = $percentage;
                continue;
            }

            if ($subject->mandatory) {
                $mandatoryPercentages[] = $percentage;
            } elseif (!$subject->mandatory && !$subject->type) {
                $optionalPercentages[] = $percentage;
            } elseif (!$subject->mandatory && $subject->type) {
                $corePercentages[] = $percentage;
            }
        }

        rsort($optionalPercentages);
        rsort($corePercentages);

        $contributingPercentages = $mandatoryPercentages;

        if ($isForeigner) {
            // Best 2 optionals
            $contributingPercentages = array_merge(
                $contributingPercentages,
                array_slice($optionalPercentages, 0, 2)
            );
            $remainingOptionals = array_slice($optionalPercentages, 2);
        } else {
            // Best 1 optional
            if (count($optionalPercentages) > 0) {
                $contributingPercentages[] = $optionalPercentages[0];
            }
            $remainingOptionals = array_slice($optionalPercentages, 1);
        }

        // Best 2 from combined remaining
        $combinedRemaining = array_merge($remainingOptionals, $corePercentages);
        rsort($combinedRemaining);
        $contributingPercentages = array_merge(
            $contributingPercentages,
            array_slice($combinedRemaining, 0, 2)
        );

        if (empty($contributingPercentages)) {
            return 0;
        }

        return round(array_sum($contributingPercentages) / count($contributingPercentages), 2);
    }
}

<?php

namespace App\Http\Controllers\Finals;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Models\SchoolSetup;
use App\Models\Term;
use App\Services\Finals\SeniorFinalsResultCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinalsSeniorStudentController extends Controller {
    public function __construct(private readonly SeniorFinalsResultCalculator $calculator) {
        $this->middleware('auth');
        $this->middleware('throttle:auth');
    }

    /**
     * Senior students ranked by BGCSE overall points for the selected
     * graduation year. Mirrors the junior "Top Students Performers" report
     * but scoped to senior-level final classes.
     */
    public function topPerformers(Request $request): View {
        $graduationYear = $this->resolveGraduationYear($request);

        $students = $this->calculator->seniorStudentsForGraduationYear($graduationYear);

        $summaries = $students
            ->map(fn ($student) => $this->calculator->summarizeStudent($student))
            ->all();

        $summaries = $this->calculator->rankByPoints($summaries);

        $withResults = array_filter($summaries, fn ($s) => $s['has_results']);
        $totalWithResults = count($withResults);

        $stats = [
            'total_students'        => count($summaries),
            'students_with_results' => $totalWithResults,
            'students_pending'      => count($summaries) - $totalWithResults,
            'highest_points'        => $totalWithResults > 0
                ? max(array_map(fn ($s) => $s['overall_points'] ?? 0, $withResults))
                : 0,
            'lowest_points'         => $totalWithResults > 0
                ? min(array_column($withResults, 'overall_points'))
                : 0,
            'average_points'        => $totalWithResults > 0
                ? round(array_sum(array_column($withResults, 'overall_points')) / $totalWithResults, 1)
                : 0,
            'grade_distribution'    => $this->calculator->gradeDistribution($summaries),
        ];

        $reportData = [
            'graduation_year' => $graduationYear,
            'exam_year'       => $graduationYear - 1,
            'students'        => $summaries,
            'stats'           => $stats,
            'overall_grades'  => $this->calculator->distinctOverallGrades($summaries),
        ];

        $school_data = SchoolSetup::first();

        return view('finals.senior.students.top-performers', compact('reportData', 'school_data'));
    }

    /**
     * Senior BGCSE transcript list for the selected graduation year.
     */
    public function transcriptsList(Request $request): View {
        $graduationYear = $this->resolveGraduationYear($request);

        $students = $this->calculator->seniorStudentsForGraduationYear($graduationYear);

        $summaries = $students
            ->map(fn ($student) => $this->calculator->summarizeStudent($student))
            ->filter(fn ($summary) => $summary['has_results'])
            ->values()
            ->all();

        $summaries = $this->calculator->rankByPoints($summaries);

        $reportData = [
            'graduation_year'  => $graduationYear,
            'exam_year'        => $graduationYear - 1,
            'transcripts'      => $summaries,
            'total_transcripts'=> count($summaries),
            'overall_grades'   => $this->calculator->distinctOverallGrades($summaries),
            'grade_distribution' => $this->calculator->gradeDistribution($summaries),
        ];

        $school_data = SchoolSetup::first();

        return view('finals.senior.students.transcripts-list', compact('reportData', 'school_data'));
    }

    /**
     * Read graduation year from request, falling back to selected term's year.
     */
    private function resolveGraduationYear(Request $request): int {
        $year = (int) $request->query('year', 0);
        if ($year > 0) {
            return $year;
        }

        $termId = session('selected_term_id', optional(TermHelper::getCurrentTerm())->id);
        if ($termId) {
            $term = Term::find($termId);
            if ($term) {
                return (int) $term->year;
            }
        }

        return (int) date('Y');
    }
}

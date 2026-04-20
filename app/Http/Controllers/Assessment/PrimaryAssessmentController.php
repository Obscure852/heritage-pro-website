<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\AssessmentController;
use App\Helpers\TermHelper;
use App\Models\SchoolSetup;
use App\Models\Student;
use App\Models\User;
use App\Models\SubjectComment;
use App\Models\Klass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SubjectGradePerformanceReportExport;
use App\Exports\RegionalGradePerformanceReportExport;
use App\Exports\GradePerformanceAnalysisExport;
use App\Exports;
use App\Exports\ClassPerformanceAnalysisExport;
use App\Models\GradeSubject;
use App\Models\Term;
use App\Services\PrimaryAnalysisReportBuilder;
use App\Services\PrimaryReportCardBuilder;

/**
 * Primary Assessment Controller
 *
 * Handles all assessment functionality specific to Primary Schools.
 * Includes report card generation, analysis reports, and primary-specific calculations.
 */
class PrimaryAssessmentController extends BaseAssessmentController{
    /**
     * Generate PDF report card for primary student
     */
    public function primaryPDFReportCard($id){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $data = $this->primaryReportCardBuilder()->buildStudentReport((int) $id, $selectedTermId, 0);

        $reportCard = PDF::loadView('assessment.primary.primary-report-card-pdf', $data);
        return $reportCard->stream('student-report-card.pdf');
    }

    /**
     * Generate HTML report card for primary student
     */
    public function primaryHTMLReportCard($id)
    {
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $data = $this->primaryReportCardBuilder()->buildStudentReport((int) $id, $selectedTermId, 1);

        return view('assessment.primary.primary-report-card-html', $data);
    }

    /**
     * Generate PDF report cards for all students in a primary class
     */
    public function pdfReportCardsForClassPrimary($classId)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $data = $this->primaryReportCardBuilder()->buildClassReport((int) $classId, $selectedTermId, 1);

        $pdf = PDF::loadView('assessment.primary.report-card-primary-list-pdf', $data);
        return $pdf->stream('class-report-cards.pdf');
    }

    /**
     * Generate PDF report card for primary student (returns PDF object for email)
     */
    public function generatePrimaryReportCardPDF($id){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $data = $this->primaryReportCardBuilder()->buildStudentReport((int) $id, $selectedTermId, 0);

        $pdf = PDF::loadView('assessment.primary.primary-report-card-pdf', $data);
        $student = $data['student'];
        $filename = strtolower($student->first_name . '_' . $student->last_name . '_term_' . $currentTerm->term . '_report_card.pdf');
        $pdf->setOptions(['filename' => $filename]);
        return $pdf;
    }

    /**
     * Email report cards to all students in a primary class
     */
    public function generateEmailPrimaryClassListReportCards($classId){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $data = $this->primaryReportCardBuilder()->buildClassReport((int) $classId, $selectedTermId, 1);

        $pdf = PDF::loadView('assessment.primary.report-card-primary-list-pdf', $data);

        $klass = $data['klass'];
        $filename = strtolower($klass->name . '_term_' . $currentTerm->term . '_report_card.pdf');
        $pdf->setOptions(['filename' => $filename]);
        return $pdf;
    }

    private function primaryReportCardBuilder(): PrimaryReportCardBuilder
    {
        return app(PrimaryReportCardBuilder::class);
    }

    /**
     * Generate subject grade performance report for primary school
     */
    #Primary grade subject analysis
    public function generateSubjectGradePerformanceReport(Request $request, $classId, $type, $sequenceId){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $klass = Klass::query()->with('grade')->findOrFail($classId);
        $data = $this->primaryAnalysisReportBuilder()->buildSubjectGradePerformance($klass->grade_id, $selectedTermId, $type, (int) $sequenceId);
        $data['klass'] = $klass;

        return view('assessment.primary.test-primary-grade-subject-analysis', $data);
    }

    /**
     * Export subject grade performance report for primary school
     */
    public function generateSubjectGradePerformanceReportExport($classId, $type, $sequenceId){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $klass = Klass::query()->with('grade')->findOrFail($classId);
        $data = $this->primaryAnalysisReportBuilder()->buildSubjectGradePerformance($klass->grade_id, $selectedTermId, $type, (int) $sequenceId);
        $data['klass'] = $klass;

        $export = new SubjectGradePerformanceReportExport($data);
        return $export->export();
    }

    /**
     * Generate regional grade performance report for primary school
     */
    public function generateRegionalGradePerformanceReport(Request $request, $classId){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $klass = Klass::query()->with('grade')->findOrFail($classId);
        $data = $this->primaryAnalysisReportBuilder()->buildRegionalExamPerformance($klass->grade_id, $selectedTermId);
        $data['klass'] = $klass;

        return view('assessment.primary.regional-test-primary-grade-subject-analysis', $data);
    }

    /**
     * Export regional grade performance report for primary school
     */
    public function regionalGradePerformanceReportExport(Request $request, $classId)
    {
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $klass = Klass::query()->with('grade')->findOrFail($classId);
        $data = $this->primaryAnalysisReportBuilder()->buildRegionalExamPerformance($klass->grade_id, $selectedTermId);
        $data['klass'] = $klass;

        return Excel::download(new RegionalGradePerformanceReportExport($data), 'region-performance.xlsx');
    }

    /**
     * Generate HTML class performance analysis for primary school
     */
    public function htmlClassPerformanceAnalysis($classId, $type, $sequenceId){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $data = $this->primaryAnalysisReportBuilder()->buildClassPerformance((int) $classId, $selectedTermId, $type, (int) $sequenceId);

        return view('assessment.primary.test-primary-class-analysis', $data);
    }

    /**
     * Export class performance analysis for primary school
     */
    public function htmlClassPerformanceAnalysisExport(Request $request, $classId, $type, $sequenceId){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $data = $this->primaryAnalysisReportBuilder()->buildClassPerformance((int) $classId, $selectedTermId, $type, (int) $sequenceId);

        return Excel::download(new ClassPerformanceAnalysisExport($data), 'class-performance-analysis.xlsx');
    }

    /**
     * Export grade performance analysis for primary school
     */
    public function htmlGradePerformanceAnalysis($classId, $type, $sequenceId){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $klass = Klass::query()->with('grade')->findOrFail($classId);
        $data = $this->primaryAnalysisReportBuilder()->buildGradePerformance($klass->grade_id, $selectedTermId, $type, (int) $sequenceId);
        $data['klass'] = $klass;

        return view('assessment.primary.test-primary-grade-analysis', $data);
    }

    public function htmlGradePerformanceAnalysisExport(Request $request, $classId, $type, $sequenceId)
    {
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $klass = Klass::query()->with('grade')->findOrFail($classId);
        $data = $this->primaryAnalysisReportBuilder()->buildGradePerformance($klass->grade_id, $selectedTermId, $type, (int) $sequenceId);
        $data['klass'] = $klass;

        return Excel::download(new GradePerformanceAnalysisExport($data), 'grade-performance-analysis.xlsx');
    }

    /**
     * Generate overall grade performance report for primary school
     */
    public function generateGradePerformanceReport($classId, $type, $sequenceId){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $klass = Klass::query()->with('grade')->findOrFail($classId);
        $data = $this->primaryAnalysisReportBuilder()->buildOverallGradeDistribution($klass->grade_id, $selectedTermId, $type, (int) $sequenceId);
        $data['klass'] = $klass;

        return view('assessment.primary.test-primary-overall-grade-analysis', $data);
    }

    private function primaryAnalysisReportBuilder(): PrimaryAnalysisReportBuilder
    {
        return app(PrimaryAnalysisReportBuilder::class);
    }
}

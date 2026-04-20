<?php

namespace App\Models;

use App\Models\ExternalExam;
use App\Models\FinalStudent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class ExternalExamResult extends Model{
    use HasFactory;

    protected $fillable = [
        'external_exam_id',
        'final_student_id',
        'exam_number',
        'excel_class_name',
        'overall_grade',
        'overall_points',
        'total_subjects',
        'passes',
        'pass_percentage',
    ];

    protected $casts = [
        'pass_percentage' => 'decimal:2',
        'overall_points' => 'decimal:1',
    ];

    public function externalExam(){
        return $this->belongsTo(ExternalExam::class);
    }

    public function finalStudent(){
        return $this->belongsTo(FinalStudent::class);
    }

    public function subjectResults(){
        return $this->hasMany(ExternalExamSubjectResult::class);
    }

    public function mappedSubjectResults(){
        return $this->hasMany(ExternalExamSubjectResult::class)->where('is_mapped', true);
    }

    public function unmappedSubjectResults(){
        return $this->hasMany(ExternalExamSubjectResult::class)->where('is_mapped', false);
    }

    public function takenSubjectResults(){
        return $this->hasMany(ExternalExamSubjectResult::class)->where('was_taken', true);
    }

    public function passedSubjectResults(){
        return $this->hasMany(ExternalExamSubjectResult::class)->where('is_pass', true);
    }

    public function failedSubjectResults(){
        return $this->hasMany(ExternalExamSubjectResult::class)->where('is_pass', false);
    }

    public function getTotalPointsAttribute(){
        return $this->takenSubjectResults()->sum('grade_points');
    }

    public function getCalculatedOverallGradeAttribute(){
        // Use stored overall_points (not calculated total_points) to match what's displayed in reports
        $totalPoints = $this->overall_points;
        $academicYear = $this->getAcademicYear();
        
        // Use exam year from the related external exam, not graduation year
        // Exams are taken in the year before graduation
        $examYear = $this->externalExam->exam_year ?? ($this->finalStudent->graduation_year - 1);

        if (!$academicYear || $totalPoints === null || $totalPoints === '') {
            return null;
        }

        $pointsMatrix = DB::table('overall_points_matrix')
            ->where('academic_year', $academicYear)
            ->where('year', $examYear)
            ->where('min', '<=', $totalPoints)
            ->where('max', '>=', $totalPoints)
            ->first();

        return $pointsMatrix ? $pointsMatrix->grade : null;
    }

    public function getGradePointsAttribute(){
        return $this->total_points;
    }

    public function getIsPassAttribute(){
        $overallGrade = $this->overall_grade ?? $this->calculated_overall_grade;
        return $overallGrade && in_array($overallGrade, ['A', 'B', 'C', 'Merit']);
    }

    public function getGradeColorAttribute(){
        $grade = $this->overall_grade ?? $this->calculated_overall_grade;
        
        if (!$grade) return 'secondary';
        
        $colors = [
            'Merit' => 'primary',
            'A' => 'success', 'B' => 'success', 'C' => 'success',
            'D' => 'warning', 'E' => 'danger', 'U' => 'danger',
        ];
        return $colors[$grade] ?? 'secondary';
    }

    protected function getAcademicYear(){
        $gradeName = $this->finalStudent->graduationGrade->name ?? null;
        
        if (!$gradeName) return null;
        
        if (preg_match('/F(\d)/', $gradeName, $matches)) {
            return 'F' . $matches[1];
        }
        
        return $gradeName;
    }

    public function updateOverallGradeFromPoints(){
        $calculatedGrade = $this->calculated_overall_grade;
        if ($calculatedGrade) {
            $this->update(['overall_grade' => $calculatedGrade]);
        }
        return $calculatedGrade;
    }

    public function recalculateStats(){
        $takenSubjects = $this->takenSubjectResults;
        
        $this->update([
            'total_subjects' => $takenSubjects->count(),
            'passes' => $takenSubjects->where('is_pass', true)->count(),
            'pass_percentage' => $takenSubjects->count() > 0 
                ? round(($takenSubjects->where('is_pass', true)->count() / $takenSubjects->count()) * 100, 2)
                : 0,
            'overall_grade' => $this->calculated_overall_grade
        ]);
    }

    public function scopeByGrade($query, $grade){
        return $query->where('overall_grade', $grade);
    }

    public function scopePassed($query){
        return $query->whereIn('overall_grade', ['A', 'B', 'C', 'Merit']);
    }

    public function scopeFailed($query){
        return $query->whereIn('overall_grade', ['D', 'E', 'U']);
    }

    public function scopeByExam($query, $examId){
        return $query->where('external_exam_id', $examId);
    }

    public function scopeByExcelClass($query, $className){
        return $query->where('excel_class_name', $className);
    }

    public function scopeWithMappedSubjects($query){
        return $query->whereHas('subjectResults', function($q) {
            $q->where('is_mapped', true);
        });
    }

    public function scopeWithTakenSubjects($query)
    {
        return $query->whereHas('subjectResults', function($q) {
            $q->where('was_taken', true);
        });
    }

    public function scopeByPointsRange($query, $minPoints, $maxPoints){
        return $query->whereHas('subjectResults', function($q) use ($minPoints, $maxPoints) {
            $q->havingRaw('SUM(grade_points) BETWEEN ? AND ?', [$minPoints, $maxPoints]);
        });
    }

    public function scopeByAcademicYear($query, $academicYear){
        return $query->whereHas('finalStudent.graduationGrade', function($q) use ($academicYear) {
            $q->where('name', 'LIKE', '%' . $academicYear . '%');
        });
    }

    public static function recalculateAllOverallGrades($examId = null){
        $query = static::query();
        
        if ($examId) {
            $query->where('external_exam_id', $examId);
        }
        
        $results = $query->with(['subjectResults', 'finalStudent.graduationGrade'])->get();
        foreach ($results as $result) {
            $result->recalculateStats();
        }
        return $results->count();
    }
}

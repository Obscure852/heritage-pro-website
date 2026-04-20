<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExternalExamSubjectResult extends Model{
    use HasFactory;

    protected $fillable = [
        'external_exam_result_id',
        'final_grade_subject_id',
        'subject_code',
        'subject_name',
        'grade',
        'grade_points',
        'is_pass',
        'is_mapped',
        'was_taken',
        'mapping_notes',
    ];

    protected $casts = [
        'grade_points' => 'decimal:1',
        'is_pass' => 'boolean',
        'is_mapped' => 'boolean',
        'was_taken' => 'boolean',
    ];

    public function externalExamResult(){
        return $this->belongsTo(ExternalExamResult::class);
    }

    public function finalGradeSubject(){
        return $this->belongsTo(FinalGradeSubject::class);
    }

    public function setGradeAttribute($value){
        $this->attributes['grade'] = strtoupper($value);
        $gradePoints = [
            'A' => 4.0, 'B' => 3.0, 'C' => 2.0, 
            'D' => 1.0, 'E' => 0.5, 'U' => 0.0,
        ];
        
        $this->attributes['grade_points'] = $gradePoints[$value] ?? 0.0;
        $this->attributes['is_pass'] = in_array($value, ['A', 'B', 'C']);
    }

    public function getGradeColorAttribute(){
        $colors = [
            'A' => 'success', 'B' => 'success', 'C' => 'success',
            'D' => 'warning', 'E' => 'danger', 'U' => 'danger',
        ];
        return $colors[$this->grade] ?? 'secondary';
    }

    public function scopeMapped($query){
        return $query->where('is_mapped', true);
    }

    public function scopeUnmapped($query){
        return $query->where('is_mapped', false);
    }

    public function scopePassed($query){
        return $query->where('is_pass', true);
    }

    public function scopeFailed($query){
        return $query->where('is_pass', false);
    }

    public function scopeByGrade($query, $grade){
        return $query->where('grade', $grade);
    }

    public function scopeBySubjectCode($query, $subjectCode){
        return $query->where('subject_code', $subjectCode);
    }

    public function scopeByExam($query, $examId){
        return $query->whereHas('externalExamResult', function($q) use ($examId) {
            $q->where('external_exam_id', $examId);
        });
    }

    public function scopeWithTeacher($query, $teacherId = null){
        return $query->whereHas('finalGradeSubject.finalKlassSubjects', function($q) use ($teacherId) {
            if ($teacherId) {
                $q->where('user_id', $teacherId);
            }
        });
    }

    public function scopeWithClass($query, $klassId = null){
        return $query->whereHas('externalExamResult.finalStudent.finalKlasses', function($q) use ($klassId) {
            if ($klassId) {
                $q->where('final_klasses.id', $klassId);
            }
        });
    }
}
<?php

namespace App\Models;

use App\Models\ExternalExamSubjectResult;
use App\Models\User;
use App\Models\Term;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExternalExam extends Model{
    use HasFactory;

    protected $fillable = [
        'exam_type',
        'exam_session',
        'centre_code',
        'centre_name',
        'exam_year',
        'graduation_year',
        'graduation_term_id',
        'import_date',
        'imported_by',
        'import_notes',
        'excel_columns',
        'original_filename',
    ];

    protected $casts = [
        'import_date' => 'date',
        'excel_columns' => 'array',
    ];

    public function graduationTerm(){
        return $this->belongsTo(Term::class, 'graduation_term_id');
    }

    public function importedBy(){
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function results(){
        return $this->hasMany(ExternalExamResult::class);
    }

    public function subjectResults(){
        return $this->hasManyThrough(
            ExternalExamSubjectResult::class,
            ExternalExamResult::class,
            'external_exam_id',
            'external_exam_result_id'
        );
    }

    public function scopeByExamType($query, $type){
        return $query->where('exam_type', $type);
    }

    public function scopeByExamYear($query, $year){
        return $query->where('exam_year', $year);
    }

    public function scopeByGraduationYear($query, $year){
        return $query->where('graduation_year', $year);
    }

    public function scopeByGraduationTerm($query, $termId){
        return $query->where('graduation_term_id', $termId);
    }

    public function scopeRecent($query){
        return $query->orderBy('exam_year', 'desc')->orderBy('created_at', 'desc');
    }


    public function getTotalStudentsAttribute(){
        return $this->results()->count();
    }

    public function getTotalPassesAttribute(){
        return $this->results()->whereIn('overall_grade', ['A', 'B', 'C'])->count();
    }

    public function getOverallPassRateAttribute(){
        $totalStudents = $this->total_students;
        if ($totalStudents === 0) {
            return 0;
        }
        return round(($this->total_passes / $totalStudents) * 100, 2);
    }

    public function getSubjectColumnsAttribute(){
        $columns = $this->excel_columns ?? [];
        return array_filter($columns, function($column) {
            return !in_array(strtolower($column), ['exam number', 'first name', 'last name', 'class']);
        });
    }
}
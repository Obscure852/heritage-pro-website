<?php

namespace App\Models;

use App\Helpers\TermHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GradeSubject extends Model{
    protected $table = 'grade_subject';
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sequence',
        'grade_id',
        'subject_id',
        'term_id',
        'department_id',
        'year',
        'type',
        'mandatory',
        'active'
    ];


    public function department(){
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function grade(){
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function subject(){
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function term(){
        return $this->belongsTo(Term::class, 'term_id');
    }

    function tests(){
        return $this->hasMany(Test::class,'grade_subject_id')->orderBy('sequence','asc');
    }

    function gradingScale($gradeId) {
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $currentTermId = TermHelper::getCurrentTerm()->id;
        return $this->hasMany(GradingScale::class, 'grade_subject_id')
                    ->where('grade_id', $gradeId)
                    ->where('term_id', $selectedTermId);
    }


    public function gradingScales(){
        return $this->hasMany(GradingScale::class,'grade_subject_id');
    }

    public function optionalSubjects(){
        return $this->hasMany(OptionalSubject::class);
    }


    public function components(){
        return $this->hasMany(Component::class,'grade_subject_id');
    }

    public function gradeOptions(){
        return $this->belongsToMany(GradeOption::class, 'subject_grade_options');
    }

    public function gradeOptionSets() {
        return $this->belongsToMany(GradeOptionSet::class, 'subject_grade_option_set', 'grade_subject_id', 'grade_option_set_id');
    }

    public function getGradeOptionsAttribute(){
        $options = collect();
        foreach ($this->gradeOptionSets as $set) {
            $options = $options->merge($set->gradeOptions);
        }
        return $options;
    }

    public function criteriaBasedTests(){
        return $this->hasMany(CriteriaBasedTest::class,'grade_subject_id');
    }
    
}

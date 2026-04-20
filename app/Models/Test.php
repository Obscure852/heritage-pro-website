<?php
namespace App\Models;

use App\Models\Schemes\SchemeOfWorkEntry;
use App\Models\Schemes\SyllabusObjective;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class Test extends Model{
    use HasFactory,SoftDeletes;

    protected $table = 'tests';
    protected $fillable = [
        'sequence',
        'name',
        'abbrev',
        'out_of',
        'grade_id',
        'grade_subject_id',
        'term_id',
        'type',
        'assessment',
        'year',
        'start_date',
        'end_date',
    ];

    function scopeOrdered(Builder $query){
        return $query->orderBy('sequence','asc');
    }

    function subject(){
        return $this->belongsTo(GradeSubject::class,'grade_subject_id');
    }

    function term(){
        return $this->belongsTo(Term::class,'term_id');
    }

    function grade(){
        return $this->belongsTo(Grade::class,'grade_id');
    }

    public function students(){
    return $this->belongsToMany(Student::class,'student_tests')
                ->using(StudentTest::class);
    }

    public function studentTests(){
        return $this->hasMany(StudentTest::class);
    }

    public function schemeEntries(): BelongsToMany {
        return $this->belongsToMany(
            SchemeOfWorkEntry::class,
            'test_scheme_entries',
            'test_id',
            'scheme_of_work_entry_id'
        )->withTimestamps();
    }

    public function syllabusObjectives(): BelongsToMany {
        return $this->belongsToMany(
            SyllabusObjective::class,
            'test_syllabus_objectives',
            'test_id',
            'syllabus_objective_id'
        )->withTimestamps();
    }

}

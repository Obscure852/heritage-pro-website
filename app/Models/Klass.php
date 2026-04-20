<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Klass extends Model {
    use HasFactory, SoftDeletes;

    const TYPE_TRIPLE_AWARD = 'Triple Award';
    const TYPE_DOUBLE_AWARD = 'Double Award';
    const TYPE_SINGLE_AWARD = 'Single Award';

    const TYPES = [
        self::TYPE_TRIPLE_AWARD,
        self::TYPE_DOUBLE_AWARD,
        self::TYPE_SINGLE_AWARD,
    ];

    protected $fillable = [
        'name',
        'user_id',
        'term_id',
        'grade_id',
        'monitor_id',
        'monitress_id',
        'type',
        'max_students',
        'year'
    ];

    protected $casts = [
        'max_students' => 'integer',
    ];

    public function students(){
        return $this->belongsToMany(Student::class,'klass_student')->withTimestamps()->withPivot('active','term_id','year')->orderBy('first_name','asc');
    }

    public function scopeForTermYear($query, $termId){
        return $query->where('term_id', $termId);
    }

    public function currentStudents($term_id, $year) {
      return $this->belongsToMany(Student::class, 'klass_student')
                  ->withPivot('term_id', 'year')
                  ->wherePivot('term_id', $term_id)
                  ->wherePivot('year', $year)->withTimestamps();
    }

    public function subjectClasses(){
        return $this->hasMany(KlassSubject::class,'klass_id');
    }
    

    public function teacher(){
      return $this->belongsTo(User::class,'user_id');
    }

    public function term() {
      return $this->belongsTo(Term::class,'term_id');
    }

    public function grade(){
      return $this->belongsTo(Grade::class,'grade_id');
    }

    public function optionalSubjects(){
        return $this->belongsToMany(OptionalSubject::class, 'student_optional_subjects', 'klass_id', 'optional_subject_id')->withPivot(['term_id'])
        ->distinct()->withTimestamps();
    }

    public function subjects(){
      return $this->hasMany(KlassSubject::class, 'klass_id');
  }

    public function monitor(){
      return $this->belongsTo(Student::class, 'monitor_id');
    }

    public function monitress(){
      return $this->belongsTo(Student::class, 'monitress_id');
    }

    public function attendances(){
      return $this->hasMany(Attendance::class, 'klass_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentTerm extends Pivot{
    use HasFactory,SoftDeletes;

    protected $table = 'student_term';

    protected $primary = ['student_id','term_id','year'];
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'term_id',
        'grade_id',
        'year',
        'status',
    ];

    /**
     * Get the student's class for this term.
     * Note: Renamed from class() to klass() to avoid PHP reserved word.
     * The relationship goes through klass_student pivot table.
     */
    public function klass()
    {
        return $this->belongsToMany(
            Klass::class,
            'klass_student',
            'student_id',
            'klass_id'
        )->where('klass_student.term_id', $this->term_id);
    }

    /**
     * Get the student associated with this term record.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the term associated with this record.
     */
    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    /**
     * Get the grade for this student-term record.
     */
    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }
}

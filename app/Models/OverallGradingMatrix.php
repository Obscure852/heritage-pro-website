<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OverallGradingMatrix extends Model{
    use HasFactory,SoftDeletes;

    protected $table = 'overall_grading_matrices';

    protected $fillable = [
        'term_id',
        'year',
        'grade_id',
        'grade',
        'min_score',
        'max_score',
        'description',
    ];
}

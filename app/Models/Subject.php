<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model{
    use HasFactory,SoftDeletes;

    public $timestamps = true;
    protected $table = 'subjects';

    protected $fillable = [
        'abbrev',
        'name',
        'canonical_key',
        'level',
        'components',
        'description',
        'department',
        'syllabus_url',
        'is_double'
    ];

    protected $casts = [
        'is_double' => 'boolean',
    ];

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommentBank extends Model {
    use HasFactory, SoftDeletes;

    protected $table = 'comment_banks';

    protected $fillable = [
        'min_points',
        'max_points',
        'body',
    ];
}

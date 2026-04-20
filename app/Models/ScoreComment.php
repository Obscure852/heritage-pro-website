<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreComment extends Model{
    protected $table = 'score_comments';

    protected $fillable = [
        'min_score',
        'max_score',
        'comment',
    ];

    public static function getRandomCommentForScore($score){
        $comments = self::where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->get();

        return $comments->isNotEmpty() 
            ? $comments->random()->comment 
            : null;
    }
}
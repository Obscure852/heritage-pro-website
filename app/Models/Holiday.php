<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Holiday extends Model{
    use HasFactory,SoftDeletes;


    protected $fillable = [
        'term_id',
        'name',
        'start_date',
        'end_date',
        'year'
    ];


    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function term(){
        return $this->belongsTo(Term::class);
    }

    public static function calculateSchoolDays($termStart, $termEnd, $termId) {
        $termDays = $termStart->diffInDays($termEnd) + 1;

        $holidays = self::where('term_id', $termId)
                        ->get()
                        ->sum(function ($holiday) {
                            return Carbon::parse($holiday->start_date)->diffInDays(Carbon::parse($holiday->end_date)) + 1;
                        });

        return $termDays - $holidays;
    }
}

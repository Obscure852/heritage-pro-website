<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Term extends Model{

    use HasFactory, SoftDeletes;
    public $timestamps = true;
    
    protected $fillable = [
        'start_date',
        'end_date',
        'term_type',
        'term',
        'year',
        'closed',
        'extension_days',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    function admissions(){
        return $this->belongsToMany(Admission::class);
    }

    function users(){
        return $this->belongsToMany(User::class);
    }

    function students() {
        return $this->belongsToMany(Student::class)
                    ->using(StudentTerm::class)->withPivot('year','status')->withTimestamps();
    }

    public static function currentOrLastActiveTerm(){
        $today = Carbon::today();
        $currentTerm = self::where('start_date', '<=', $today)
                           ->where('end_date', '>=', $today)
                           ->first();

        if ($currentTerm) {
            return $currentTerm;
        } else {
            return self::where('end_date', '<', $today)
                       ->where('closed', 0)
                       ->orderBy('end_date', 'desc')
                       ->first();
        }
    }

    public static function nextTerm($term){
        if ($term) {
            return self::where('year', $term->year)
                       ->where('term', $term->term + 1)
                       ->first();
        } else {
            $today = Carbon::today();
            return self::where('start_date', '>', $today)
                       ->orderBy('start_date', 'asc')
                       ->first();
        }
    }

    public static function getRelevantTerms(){
        $today = Carbon::today();
        $currentTerm = self::where('start_date', '<=', $today)
                           ->where('end_date', '>=', $today)
                           ->first();
        
        if ($currentTerm) {
            return self::where(function($query) use ($currentTerm, $today) {
                    $query->where('year', '<', $currentTerm->year)
                          ->orWhere(function($q) use ($currentTerm) {
                              $q->where('year', $currentTerm->year)
                                ->where('term', '<=', $currentTerm->term);
                          });
                })
                ->orderBy('year', 'desc')
                ->orderBy('term', 'desc')
                ->get();
        } else {
            return self::where('end_date', '<=', $today)
                       ->orderBy('year', 'desc')
                       ->orderBy('term', 'desc')
                       ->get();
        }
    }
}

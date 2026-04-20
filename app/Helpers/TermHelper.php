<?php
namespace App\Helpers;

use App\Models\Term;
use App\Models\Test;
use DB;
use Exception;
use Log;

class TermHelper{

    public static function getCurrentTerm() {
        $today = now();

        // PRIORITY 1: Oldest unclosed past term (enforces sequential closure)
        // Users must close Term 1 before Term 2 becomes current, etc.
        $unclosedPastTerm = Term::where('closed', 0)
            ->where('end_date', '<', $today)
            ->orderBy('end_date', 'asc')  // OLDEST first
            ->first();

        if ($unclosedPastTerm) {
            return $unclosedPastTerm;
        }

        // PRIORITY 2: Current active term (within date range)
        $currentTerm = Term::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where('closed', 0)
            ->orderBy('start_date', 'desc')
            ->first();

        if ($currentTerm) {
            return $currentTerm;
        }

        // PRIORITY 3: Future term
        $futureTerm = Term::where('start_date', '>', $today)
            ->where('closed', 0)
            ->orderBy('start_date', 'asc')
            ->first();

        if ($futureTerm) {
            return $futureTerm;
        }

        // FALLBACK: Most recent term
        return Term::orderBy('start_date', 'desc')->first();
    }

    public static function getTerms(){
        return Term::orderBy('year', 'asc')
                   ->orderBy('id', 'asc')
                   ->where('closed',0)
                   ->take(12)
                   ->get();
    }


    public static function currentTermId() {
        return session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
    }
    public static function getPreviousTerm(Term $currentTerm){
        $currentNumber = $currentTerm->term;
        $currentYear   = $currentTerm->year;

        if ($currentNumber > 1) {
            return Term::where('year', $currentYear)
                       ->where('term', $currentNumber - 1)
                       ->first();
        }

        $prevYear = $currentYear - 1;
        $maxTerm  = Term::where('year', $prevYear)
                        ->max('term');

        return Term::where('year', $prevYear)
                   ->where('term', $maxTerm)
                   ->first();
    }

    public static function getSelectableTerms(?Term $currentTerm): \Illuminate\Support\Collection {
        if (!$currentTerm) {
            $year = now()->year;
            return Term::whereYear('start_date', $year - 1)->orderBy('start_date')->get()
                ->concat(Term::whereYear('start_date', $year)->orderBy('start_date')->get())
                ->concat(Term::whereYear('start_date', $year + 1)->orderBy('start_date')->limit(2)->get());
        }

        $before = Term::where('id', '<', $currentTerm->id)->orderBy('id', 'desc')->limit(3)->get();
        $after  = Term::where('id', '>', $currentTerm->id)->orderBy('id', 'asc')->limit(5)->get();

        return $before->reverse()->values()->push($currentTerm)->concat($after);
    }

    public static function createTest($toTerm, $subject, $name, $abbrev, $type, $sequence, $outOf, $testDate) {
        Test::create([
            'sequence' => $sequence,
            'name' => $name,
            'abbrev' => $abbrev,
            'grade_subject_id' => $subject->id,
            'term_id' => $toTerm->id,
            'grade_id' => $subject->grade_id,
            'out_of' => $outOf,
            'year' => $toTerm->year,
            'type' => $type,
            'assessment' => 1,
            'start_date' => $testDate->copy()->startOfMonth()->toDateString(),
            'end_date' => $testDate->copy()->endOfMonth()->toDateString(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

}

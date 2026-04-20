<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Term;
use Carbon\Carbon;

class TermPolicy{
    use HandlesAuthorization;

    public function __construct(){}

    public function shouldTakeActionForTermEnd(User $user, Term $term){
        $daysToTermEnd = Carbon::now()->diffInDays(Carbon::parse($term->end_date), false);
        return $user->hasAnyRoles(['Administrator', 'System Setup']) && $daysToTermEnd < 10;
    }
    
}

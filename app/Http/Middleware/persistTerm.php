<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class persistTerm{

    public function handle(Request $request, Closure $next){
        if(session()->has('selected_term_id')){
            view()->share('currentTermId',session('selected_term_id'));
        }
        return $next($request);
    }
}

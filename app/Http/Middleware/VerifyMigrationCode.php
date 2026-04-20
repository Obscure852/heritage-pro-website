<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyMigrationCode{

    public function handle(Request $request, Closure $next){
        if ($request->query('key') !== env('MIGRATION_API_KEY')) {
            return redirect()->back()->withErrors(['message' => 'The migration code is incorrect.']);
        }
        return $next($request);
    }
}
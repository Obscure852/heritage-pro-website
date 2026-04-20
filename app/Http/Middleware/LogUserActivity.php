<?php

namespace App\Http\Middleware;

use App\Http\Controllers\SchoolSetupController;
use App\Models\Logging;
use Closure;
use App\Models\Student;
use Illuminate\Http\Request;

class LogUserActivity{

    public $api_key = '87ef385b2c6054';

    public function handle($request, Closure $next){
        $response = $next($request);

        if (auth()->check()) {
            $changes = [];

            // Listen for Eloquent events and capture changes
            Student::updated(function ($model) use (&$changes) {
                $changes[] = [
                    'type' => 'update',
                    'original' => $model->getOriginal(),
                    'changes' => $model->getChanges(),
                ];
            });

            // Similarly, you can listen for other events like created, deleted, etc.
            $location = SchoolSetupController::getLocationByIp($request->ip(),'87ef385b2c6054');
            Logging::create([
                'user_id' => auth()->id(),
                'ip_address' => $location,
                'user_agent' => $request->userAgent(),
                'url' => $request->url(),
                'method' => $request->method(),
                'input' => json_encode($request->input()),
                'changes' => json_encode($changes),  // Store the changes
                // Add any other fields you need
            ]);
        }
        return $response;
    }

    
}

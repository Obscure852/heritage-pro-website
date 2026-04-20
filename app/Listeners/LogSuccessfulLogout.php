<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Helpers\LogActivityHelper;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogout{
    
    public function handle(Logout $event){
        try {
            $user = $event->user;
            LogActivityHelper::logLoginActivity('Logout', $user);
        } catch (\Exception $e) {
            Log::error("Error in LogSuccessfulLogout: " . $e->getMessage());
        }
    }
}
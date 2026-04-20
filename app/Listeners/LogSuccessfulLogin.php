<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Helpers\LogActivityHelper;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogin{
    public function handle(Login $event){
        try {
            $user = $event->user;
            LogActivityHelper::logLoginActivity('Login', $user);
        } catch (\Exception $e) {
            Log::error("Error in LogSuccessfulLogin: " . $e->getMessage());
        }
    }
}

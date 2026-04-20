<?php

namespace App\Helpers;

use App\Models\Logging;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\SchoolSetupController;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class LogActivityHelper{
    public static function logActivity($action, $model){
        try {
            $user = null;
            if (Auth::check()) {
                $user = Auth::user();
            } elseif (Auth::guard('student')->check()) {
                $user = Auth::guard('student')->user();
            } elseif (Auth::guard('sponsor')->check()) {
                $user = Auth::guard('sponsor')->user();
            }
            
            $userId = $user ? $user->id : null;
            $userType = self::getUserType($user);
            
            $ipAddress = Request::ip();
            $location = SchoolSetupController::getLocationByIp($ipAddress);
            
            // Fix: For delete operations, capture all model attributes, not just changes
            if (in_array($action, ['Deleted', 'Force Deleted'])) {
                $dataToLog = $model->attributesToArray();
            } elseif ($action === 'Created') {
                $dataToLog = $model->attributesToArray();
            } else {
                // For updates, still use getChanges() to show what was modified
                $dataToLog = $model->getChanges();
            }
            
            $userAgent = Request::userAgent();
            $browserInfo = self::getBrowserNameAndVersion($userAgent);
            
            $logData = [
                'ip_address' => $ipAddress,
                'location'   => $location,
                'user_agent' => $browserInfo,
                'url'        => Request::url(),
                'method'     => Request::method(),
                'input'      => json_encode(self::filterInput(Request::all())),
                'changes'    => json_encode([
                    'action' => $action,
                    'data'   => $dataToLog,
                    'user_type' => $userType,
                    'user_id' => $userId,
                    'email' => $user ? $user->email : null,
                ]),
            ];
            
            if ($userType === 'user') {
                $logData['user_id'] = $userId;
            }
            
            Logging::create($logData);
        } catch (\Exception $e) {
            Log::error("Failed to log activity: " . $e->getMessage());
        }
    }
    
    public static function logLoginActivity($action, $user){
        try {
            if (!$user) {
                if (Auth::check()) {
                    $user = Auth::user();
                } elseif (Auth::guard('student')->check()) {
                    $user = Auth::guard('student')->user();
                } elseif (Auth::guard('sponsor')->check()) {
                    $user = Auth::guard('sponsor')->user();
                } else {
                    $userId = null;
                }
            }
            
            $userId = $user ? $user->id : null;
            $userType = self::getUserType($user);
            
            $ipAddress = Request::ip();
            $location = SchoolSetupController::getLocationByIp($ipAddress);
            $userAgent = Request::userAgent();
            $browserInfo = self::getBrowserNameAndVersion($userAgent);
            
            $logData = [
                'ip_address' => $ipAddress,
                'location'   => $location,
                'user_agent' => $browserInfo,
                'url'        => Request::url(),
                'method'     => Request::method(),
                'input'      => json_encode(self::filterInput(Request::all())),
                'changes'    => json_encode([
                    'action' => $action,
                    'data'   => [
                        'user_type' => $userType,
                        'user_id' => $userId,
                        'email' => $user ? $user->email : null,
                    ],
                ]),
            ];
            
            if ($userType === 'user') {
                $logData['user_id'] = $userId;
            }
            
            Logging::create($logData);
        } catch (\Exception $e) {
            Log::error("Failed to log login/logout activity: " . $e->getMessage());
        }
    }

    protected static function getUserType($user){
        if (!$user) {
            return 'unknown';
        }
        
        $className = get_class($user);
        if (strpos($className, 'Student') !== false) {
            return 'student';
        } elseif (strpos($className, 'Sponsor') !== false) {
            return 'sponsor';
        } else {
            return 'user';
        }
    }
    
    protected static function getBrowserNameAndVersion($userAgent){
        $agent = new Agent();
        $agent->setUserAgent($userAgent);
        
        $browser = $agent->browser();
        $version = $agent->version($browser);
        
        $platform = $agent->platform();
        $platformVersion = $agent->version($platform);
        
        return "{$browser} {$version} on {$platform} {$platformVersion}";
    }
    
    protected static function filterInput(array $input){
        $excludeKeys = ['password', 'password_confirmation', '_token'];
        foreach ($excludeKeys as $key) {
            if (isset($input[$key])) {
                $input[$key] = '********';
            }
        }
        return $input;
    }
}
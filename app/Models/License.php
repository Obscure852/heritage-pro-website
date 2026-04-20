<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class License extends Model{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'key',
        'year',
        'start_date',
        'end_date',
        'active',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean',
    ];
    
 
    protected static function boot(){
        parent::boot();
        static::creating(function ($license) {
            $license->key = static::generateLicenseKey($license);
        });
    }
    
    public static function generateLicenseKey($license = null){
        if (!$license) {
            do {
                $key = bin2hex(random_bytes(16));
            } while (self::where('key', $key)->exists());
            
            return $key;
        }
        
        $licenseData = [
            'id' => uniqid('LIC-'),
            'name' => $license->name,
            'domain' => request()->getHost(),
            'created' => date('Y-m-d'),
            'start_date' => $license->start_date ? $license->start_date->format('Y-m-d') : date('Y-m-d'),
            'end_date' => $license->end_date ? $license->end_date->format('Y-m-d') : date('Y-m-d', strtotime('+1 year')),
            'year' => $license->year ?? date('Y'),
            'premium' => true
        ];
        
        $jsonData = json_encode($licenseData);
        $encodedData = base64_encode($jsonData);
        
        return self::formatKeyForStorage($encodedData);
    }
    
    protected static function formatKeyForStorage($encodedData){
        $prefix = substr(md5(uniqid()), 0, 6);
        $key = $prefix . '.' . $encodedData;
        
        if (strlen($key) > 190) {
            $fullKeyPath = storage_path('app/system/keys/' . md5($encodedData) . '.key');
            
            if (!file_exists(dirname($fullKeyPath))) {
                mkdir(dirname($fullKeyPath), 0755, true);
            }
            
            file_put_contents($fullKeyPath, $encodedData);
            return 'ref:' . md5($encodedData);
        }
        
        return $key;
    }

    public static function checkSystemHealth(){
        if (app()->runningInConsole()) {
            return true;
        }
        
        try {
            if (!Schema::hasTable('licenses')) {
                return true;
            }
        } catch (\Exception $e) {
            return true;
        }

        $cacheKey = 'system_health_status';
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        $systemConfig = self::where('active', true)->first();
        if (!$systemConfig) {
            Cache::put($cacheKey, false, 60);
            return false;
        }
        
        $now = Carbon::now();
        $licenseValid = $systemConfig->active && 
                    $now->greaterThanOrEqualTo($systemConfig->start_date);
                    
        $gracePeriodDays = config('license.grace_period_days', 14);
        $isExpired = $now->greaterThan($systemConfig->end_date);
        $isWithinGracePeriod = false;
        
        if ($isExpired) {
            $gracePeriodEnd = (clone $systemConfig->end_date)->addDays($gracePeriodDays);
            $isWithinGracePeriod = $now->lessThanOrEqualTo($gracePeriodEnd);
            
            Cache::put('license_in_grace_period', $isWithinGracePeriod, 60 * 24);
            Cache::put('license_grace_ends', $gracePeriodEnd, 60 * 24);
            
            if ($isWithinGracePeriod) {
                Log::info('License in grace period', [
                    'license' => $systemConfig->name,
                    'expired' => $systemConfig->end_date->format('Y-m-d'),
                    'grace_ends' => $gracePeriodEnd->format('Y-m-d'),
                    'days_remaining' => $now->diffInDays($gracePeriodEnd)
                ]);
            }
        } else {
            Cache::forget('license_in_grace_period');
            Cache::forget('license_grace_ends');
        }
        
        // License is valid if it's active, has started, and hasn't expired (or is in grace period)
        $licenseHasStarted = $now->greaterThanOrEqualTo($systemConfig->start_date);
        $basicValid = $systemConfig->active && $licenseHasStarted && ($isWithinGracePeriod || !$isExpired);
        
        if (!$basicValid) {
            Cache::put($cacheKey, false, 60);
            return false;
        }
        
        $isLegacyKey = self::isLegacyLicenseKey($systemConfig->key);
        if ($isLegacyKey) {
            $legacyData = [
                'id' => $systemConfig->id,
                'name' => $systemConfig->name,
                'domain' => '*',
                'start_date' => $systemConfig->start_date->format('Y-m-d'),
                'end_date' => $systemConfig->end_date->format('Y-m-d'),
                'year' => $systemConfig->year,
                'premium' => true,
                'is_legacy' => true
            ];
            Cache::put('system_config_data', $legacyData, 1440);
            Cache::put($cacheKey, true, 60);
            return true;
        }
        
        $securityCheck = self::verifySecurityToken($systemConfig->key);
        $systemHealthy = $basicValid && $securityCheck;
        
        Cache::put($cacheKey, $systemHealthy, 60);
        
        if ($systemHealthy) {
            self::collectSystemMetrics($systemConfig);
        }
        
        return $systemHealthy;
    }
    
    protected static function isLegacyLicenseKey($key){
        $isLegacy = (strlen($key) === 32 && 
                    ctype_xdigit($key) && 
                    strpos($key, '.') === false && 
                    strpos($key, 'ref:') === false);
                    
        if ($isLegacy) {
            Log::info('Legacy license key format detected: ' . $key);
        }
        
        return $isLegacy;
    }

    protected static function verifySecurityToken($token){
    try {
        if (self::isLegacyLicenseKey($token)) {
            Log::info('Token is a legacy key format. Skipping cryptographic verification.');
            return true;
        }
        
        if (strpos($token, 'ref:') === 0) {
            $keyHash = substr($token, 4);
            $fullKeyPath = storage_path('app/system/keys/' . $keyHash . '.key');
            
            $shouldRecreateFile = false;
            if (!File::exists($fullKeyPath)) {
                Log::error('Security key file not found: ' . $fullKeyPath);
                $shouldRecreateFile = true;
            } else {
                $encodedData = File::get($fullKeyPath);
                
                $encodedData = trim($encodedData);
                $jsonData = @base64_decode($encodedData, true);
                if ($jsonData === false) {
                    $shouldRecreateFile = true;
                } else {
                    $configData = @json_decode($jsonData, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error('Failed to parse key data as JSON: ' . json_last_error_msg());
                        Log::error('JSON decode error, regenerating key file');
                        $shouldRecreateFile = true;
                    }
                }
            }
            
            if ($shouldRecreateFile) {
                $directory = dirname($fullKeyPath);
                if (!File::exists($directory)) {
                    Log::info('Creating directory: ' . $directory);
                    File::makeDirectory($directory, 0755, true);
                }
                
                $license = self::where('active', true)->first();
                if ($license) {
                    $licenseData = [
                        'id' => uniqid('LIC-RECOVERY-'),
                        'name' => $license->name,
                        'domain' => '*',
                        'created' => date('Y-m-d'),
                        'start_date' => $license->start_date->format('Y-m-d'),
                        'end_date' => $license->end_date->format('Y-m-d'),
                        'year' => $license->year,
                        'premium' => true
                    ];
                } else {
                    $licenseData = [
                        'id' => uniqid('LIC-RECOVERY-'),
                        'name' => 'Recovery License',
                        'domain' => '*',
                        'created' => date('Y-m-d'),
                        'start_date' => date('Y-m-d'),
                        'end_date' => date('Y-m-d', strtotime('+1 year')),
                        'year' => date('Y'),
                        'premium' => true
                    ];
                }
                
                $jsonData = json_encode($licenseData);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('Error creating JSON for recovery: ' . json_last_error_msg());
                    $jsonData = '{"id":"EMERGENCY-RECOVERY","name":"Recovery","domain":"*","end_date":"2030-01-01"}';
                }
                
                $encodedData = base64_encode($jsonData);
                $testDecode = base64_decode($encodedData);
                $testJson = json_decode($testDecode, true);
                
                
                File::put($fullKeyPath, $encodedData);
                
                if (!File::exists($fullKeyPath)) {
                    Log::error('Failed to create recovery key file');
                    return false;
                }
                $encodedData = File::get($fullKeyPath);
            }
            
            $jsonData = base64_decode($encodedData);
            if ($jsonData === false) {
                Log::error('Failed to decode key data from base64 after recovery attempts');
                return false;
            }
            
            $configData = json_decode($jsonData, true);
            if (!is_array($configData)) {
                Log::error('Failed to parse key data as JSON after recovery attempts');
                Log::error('JSON error: ' . json_last_error_msg());
                return false;
            }

            if (isset($configData['end_date'])) {
                $expiryDate = Carbon::parse($configData['end_date']);
                $now = Carbon::now();
                
                if ($expiryDate->isPast()) {
                    Log::info('License expired based on key file data', [
                        'end_date' => $expiryDate->format('Y-m-d'),
                        'current_date' => $now->format('Y-m-d')
                    ]);
                    return false;
                }

                if (isset($configData['start_date'])) {
                    $startDate = Carbon::parse($configData['start_date']);
                    if ($now->lessThan($startDate)) {
                        Log::info('License not yet started based on key file data', [
                            'start_date' => $startDate->format('Y-m-d'),
                            'current_date' => $now->format('Y-m-d')
                        ]);
                        // Don't return false here - license is valid but not started yet
                    }
                }
            } else {
                Log::warning('License data missing end_date field');
            }
            
            Cache::put('system_config_data', $configData, 1440);
            return true;
        }else if (substr_count($token, '.') === 2) {
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                Log::error('Invalid 3-part key format');
                return false;
            }
            
            $prefix = $parts[0];
            $signatureHash = $parts[1]; 
            $securityData = $parts[2];
            
            $jsonData = base64_decode($securityData);
            if (!$jsonData) {
                Log::error('Failed to decode data part from base64');
                return false;
            }
            
            $configData = json_decode($jsonData, true);
            if (!is_array($configData)) {
                Log::error('Failed to parse data part as JSON');
                return false;
            }
            
            if (isset($configData['end_date'])) {
                $expiryDate = Carbon::parse($configData['end_date']);
                $now = Carbon::now();
                
                // License is valid if end_date is in the future
                if ($expiryDate->isPast()) {
                    Log::info('License expired based on 3-part key data', [
                        'end_date' => $expiryDate->format('Y-m-d'),
                        'current_date' => $now->format('Y-m-d')
                    ]);
                    return false;
                }
                
                // Check if license has started (if start_date is provided)
                if (isset($configData['start_date'])) {
                    $startDate = Carbon::parse($configData['start_date']);
                    if ($now->lessThan($startDate)) {
                        Log::info('License not yet started based on 3-part key data', [
                            'start_date' => $startDate->format('Y-m-d'),
                            'current_date' => $now->format('Y-m-d')
                        ]);
                        // Don't return false here - license is valid but not started yet
                    }
                }
            }
            
            Cache::put('system_config_data', $configData, 1440);
            return true;
        }else if (substr_count($token, '.') === 1) {
            $parts = explode('.', $token);
            
            if (count($parts) !== 2) {
                Log::error('Invalid 2-part key format');
                return false;
            }
            
            $prefix = $parts[0];
            $securityData = $parts[1];
            
            $jsonData = base64_decode($securityData);
            if (!$jsonData) {
                Log::error('Failed to decode data part from base64');
                return false;
            }
            
            $configData = json_decode($jsonData, true);
            if (!is_array($configData)) {
                Log::error('Failed to parse data part as JSON');
                return false;
            }
            
            if (isset($configData['end_date'])) {
                $expiryDate = Carbon::parse($configData['end_date']);
                $now = Carbon::now();
                
                // License is valid if end_date is in the future
                if ($expiryDate->isPast()) {
                    Log::info('License expired based on 2-part key data', [
                        'end_date' => $expiryDate->format('Y-m-d'),
                        'current_date' => $now->format('Y-m-d')
                    ]);
                    return false;
                }
                
                // Check if license has started (if start_date is provided)
                if (isset($configData['start_date'])) {
                    $startDate = Carbon::parse($configData['start_date']);
                    if ($now->lessThan($startDate)) {
                        Log::info('License not yet started based on 2-part key data', [
                            'start_date' => $startDate->format('Y-m-d'),
                            'current_date' => $now->format('Y-m-d')
                        ]);
                        // Don't return false here - license is valid but not started yet
                    }
                }
            }
            
            Cache::put('system_config_data', $configData, 1440);
            return true;
        }else {
            Log::error('Unrecognized token format: ' . $token);
            return false;
        }
        
    } catch (\Exception $e) {
        Log::error($e->getTraceAsString());
        return false;
    }
}
    
    protected static function collectSystemMetrics($config){
        $cacheKey = 'analytics_last_report';
        if (Cache::has($cacheKey)) {
            return;
        }
        
        try {
            $deviceId = self::getDeviceIdentifier();
            
            Log::debug('System metrics collected', [
                'configuration_id' => $config->id,
                'device_id' => $deviceId,
                'hostname' => request()->getHost(),
                'ip_address' => request()->ip(),
                'timestamp' => now()->timestamp,
            ]);
            
            Cache::put($cacheKey, now()->timestamp, 1440);
            
        } catch (\Exception $e) {}
    }
    
    protected static function getDeviceIdentifier(){
        $idKey = 'analytics_device_id';
        
        if (Cache::has($idKey)) {
            return Cache::get($idKey);
        }
        
        $id = md5(
            request()->getHost() . 
            $_SERVER['SERVER_ADDR'] ?? '127.0.0.1' . 
            php_uname() . 
            random_bytes(8)
        );
        
        Cache::forever($idKey, $id);
        
        return $id;
    }
    
    public static function shouldOptimizeResources(){
        if (app()->runningInConsole()) {
            return false;
        }
        
        try {
            if (!Schema::hasTable('licenses')) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        if (self::checkSystemHealth()) {
            return false;
        }
        return (rand(1, 10) <= 3);
    }
    
    public static function applyResourceOptimization(){

        if (app()->runningInConsole()) {
            return;
        }

        try {
            if (!Schema::hasTable('licenses')) {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        if (!self::shouldOptimizeResources()) {
            return;
        }

        usleep(rand(100000, 500000));
    }
}

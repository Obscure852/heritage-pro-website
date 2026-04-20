<?php

namespace App\Http\Controllers;

use App\Models\License;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LicenseController extends Controller{

    protected $securityKeyPath;
    protected $publicKeyPath;
    
    public function __construct(){
        $this->securityKeyPath = storage_path('app/system/security_key.dat');
        $this->publicKeyPath = storage_path('app/system/verification.dat');
    }

    public function createSchoolLicense(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'year' => 'required|integer|min:2000|max:2099',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'active' => 'nullable|boolean',
            'grace_period_days' => 'nullable|integer|min:0|max:90',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();
        if (!$user || (!str_ends_with($user->email, '@heritagepro.co') && $user->email !== 'obscure852@gmail.com')) {
            return redirect()->back()->with('error', 'You do not have permission to create licenses');
        }
        
        $this->ensureSecurityKeysExist();

        if ($request->has('grace_period_days') && is_numeric($request->grace_period_days)) {
            $this->updateGracePeriodConfig($request->grace_period_days);
        }

        $licenseData = [
            'id' => uniqid('LIC-'),
            'name' => $request->name,
            'domain' => '*',
            'created' => date('Y-m-d'),
            'start_date' => Carbon::parse($request->start_date)->format('Y-m-d'),
            'end_date' => Carbon::parse($request->end_date)->format('Y-m-d'),
            'year' => $request->year,
            'premium' => true
        ];

        $jsonData = json_encode($licenseData);
        
        $signature = null;
        try {
            $privateKey = File::get($this->securityKeyPath);
            openssl_sign($jsonData, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            
            $encodedData = base64_encode($jsonData);
            $encodedSignature = base64_encode($signature);
            
            $configKey = $encodedSignature . '|' . $encodedData;
            $newKey = $this->formatKeyForStorage($configKey);
            
        } catch (\Exception $e) {
            Log::error('Error creating license key: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred creating the license. Please try again.');
        }
        
        $license = null;
        if ($request->has('id')) {
            $license = License::find($request->id);
        }
        
        if (!$license) {
            $license = License::where('active', true)->first();
        }
        
        if ($license) {
            $license->name = $request->name;
            $license->key = $newKey;
            $license->year = $request->year;
            $license->start_date = $request->start_date;
            $license->end_date = $request->end_date;
            $license->active = $request->has('active') ? true : false;
            $license->save();
            
            Log::info('Updated existing license to new format', [
                'id' => $license->id,
                'old_format' => $request->key ?? 'Unknown',
                'new_format' => $newKey
            ]);
            
            $message = 'License has been updated successfully!';
        } else {
            $license = new License();
            $license->name = $request->name;
            $license->key = $newKey;
            $license->year = $request->year;
            $license->start_date = $request->start_date;
            $license->end_date = $request->end_date;
            $license->active = $request->has('active') ? true : false;
            $license->save();
            
            $message = 'New license has been created successfully!';
        }
        
        Cache::forget('system_health_status');
        return redirect()->route('dashboard')->with('message', $message);
    }
    
    protected function updateGracePeriodConfig($days){
        try {
            $days = max(0, min(90, (int)$days));
            
            if (file_exists(app()->environmentFilePath())) {
                $content = file_get_contents(app()->environmentFilePath());
                
                if (preg_match('/^LICENSE_GRACE_PERIOD=(.*)$/m', $content)) {
                    $content = preg_replace(
                        '/^LICENSE_GRACE_PERIOD=(.*)$/m',
                        'LICENSE_GRACE_PERIOD=' . $days,
                        $content
                    );
                } else {
                    $content .= "\nLICENSE_GRACE_PERIOD=" . $days . "\n";
                }
                
                file_put_contents(app()->environmentFilePath(), $content);
                config(['license.grace_period_days' => $days]);
            } else {
                config(['license.grace_period_days' => $days]);
            }
        } catch (\Exception $e) {
            Log::error('Error updating grace period config: ' . $e->getMessage());
        }
    }

    protected function ensureSecurityKeysExist(){
        if (!File::exists($this->securityKeyPath)) {
            try {
                Log::info('Generating new security keys');
                $config = [
                    'digest_alg' => 'sha256',
                    'private_key_bits' => 2048,
                    'private_key_type' => OPENSSL_KEYTYPE_RSA,
                ];
                
                $keyPair = openssl_pkey_new($config);
                openssl_pkey_export($keyPair, $privateKey);
                $publicKey = openssl_pkey_get_details($keyPair)['key'];
                
                if (!File::exists(dirname($this->securityKeyPath))) {
                    File::makeDirectory(dirname($this->securityKeyPath), 0755, true);
                }
                
                File::put($this->securityKeyPath, $privateKey);
                File::put($this->publicKeyPath, $publicKey);
                
                Log::info('Security keys generated successfully');
                
            } catch (\Exception $e) {
                Log::error('Error generating security keys: ' . $e->getMessage());
                throw new \Exception('Could not generate security keys: ' . $e->getMessage());
            }
        }
    }
    

    protected function formatKeyForStorage($configKey){
        $prefix = substr(md5(uniqid()), 0, 6);
        $key = $prefix . '.' . $configKey;
        
        if (strlen($key) > 190) {
            $keyHash = md5($configKey);
            $fullKeyPath = storage_path('app/system/keys/' . $keyHash . '.key');
            
            if (!File::exists(dirname($fullKeyPath))) {
                File::makeDirectory(dirname($fullKeyPath), 0755, true);
            }
            
            File::put($fullKeyPath, $configKey);
            return 'ref:' . $keyHash;
        }
        
        return $key;
    }
    
    protected function verifyLicenseKey($key){
        try {
            if (strpos($key, 'ref:') === 0) {
                $keyHash = substr($key, 4);
                $fullKeyPath = storage_path('app/system/keys/' . $keyHash . '.key');
                
                if (!File::exists($fullKeyPath)) {
                    return false;
                }
                
                $configKey = File::get($fullKeyPath);
            } else {
                $parts = explode('.', $key);
                if (count($parts) < 2) {
                    return false;
                }
                $configKey = $parts[1];
            }
            
            $keyParts = explode('|', $configKey);
            if (count($keyParts) !== 2) {
                return false;
            }
            
            [$signature, $data] = $keyParts;
            
            $signature = base64_decode($signature);
            $licenseData = base64_decode($data);
            
            if (!$signature || !$licenseData) {
                return false;
            }
            
            $publicKey = File::get($this->publicKeyPath);
            $verified = openssl_verify($licenseData, $signature, $publicKey, OPENSSL_ALGO_SHA256);
            
            if ($verified !== 1) {
                return false;
            }
    
            $license = json_decode($licenseData, true);
            if (!$license) {
                return false;
            }
            
            if (isset($license['end_date'])) {
                $expires = Carbon::parse($license['end_date']);
                if ($expires->isPast()) {
                    return false;
                }
            }
            return true;
            
        } catch (\Exception $e) {
            Log::error('License verification error: ' . $e->getMessage());
            return false;
        }
    }
}

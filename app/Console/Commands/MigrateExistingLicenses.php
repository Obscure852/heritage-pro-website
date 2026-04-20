<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\License;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MigrateExistingLicenses extends Command{
    protected $signature = 'licenses:migrate';
    protected $description = 'Migrate existing licenses to new format';
    
    protected $securityKeyPath;
    protected $publicKeyPath;
    
    public function __construct(){
        parent::__construct();
        $this->securityKeyPath = storage_path('app/system/security_key.dat');
        $this->publicKeyPath = storage_path('app/system/verification.dat');
    }

    public function handle(){
        $this->ensureSecurityKeysExist();
        
        $licenses = License::where('active', true)->get();
        $this->info("Found {$licenses->count()} active licenses to migrate");
        
        foreach ($licenses as $license) {
            $originalKey = $license->key;

            if (strpos($originalKey, '.') !== false || strpos($originalKey, 'ref:') === 0) {
                $this->info("License already in new format, skipping: {$license->name}");
                continue;
            }
            
            $licenseData = [
                'id' => uniqid('LIC-'),
                'name' => $license->name,
                'domain' => '*',
                'created' => $license->created_at->format('Y-m-d'),
                'start_date' => $license->start_date->format('Y-m-d'),
                'end_date' => $license->end_date->format('Y-m-d'),
                'year' => $license->year,
                'premium' => true,
                'migrated_from' => $originalKey
            ];
            
            $jsonData = json_encode($licenseData);
            
            try {
                $signature = null;
                $privateKey = File::get($this->securityKeyPath);
                openssl_sign($jsonData, $signature, $privateKey, OPENSSL_ALGO_SHA256);
                
                $encodedData = base64_encode($jsonData);
                $encodedSignature = base64_encode($signature);
                
                $configKey = $encodedSignature . '|' . $encodedData;
                $prefix = substr(md5(uniqid()), 0, 6);
                $newKey = $prefix . '.' . $configKey;
                
                if (strlen($newKey) > 190) {
                    $fullKeyPath = storage_path('app/system/keys/' . md5($configKey) . '.key');
                    
                    if (!File::exists(dirname($fullKeyPath))) {
                        File::makeDirectory(dirname($fullKeyPath), 0755, true);
                    }
                    
                    File::put($fullKeyPath, $configKey);
                    $newKey = 'ref:' . md5($configKey);
                }
                
                $license->key = $newKey;
                $license->save();
                
                $this->info("Migrated license: {$license->name}");
                
            } catch (\Exception $e) {
                $this->error("Error migrating license {$license->name}: " . $e->getMessage());
                Log::error("License migration error: " . $e->getMessage());
            }
        }
        Cache::forget('system_health_status');
        $this->info("Migration complete!");
    }
    
    protected function ensureSecurityKeysExist(){
        $this->ensureDirectoriesExist();
        
        if (!File::exists($this->securityKeyPath)) {
            $this->info('Generating new security keys');
            
            try {
                $config = [
                    'digest_alg' => 'sha256',
                    'private_key_bits' => 2048,
                    'private_key_type' => OPENSSL_KEYTYPE_RSA,
                ];
                
                $keyPair = openssl_pkey_new($config);
                openssl_pkey_export($keyPair, $privateKey);
                
                $publicKey = openssl_pkey_get_details($keyPair)['key'];
                
                File::put($this->securityKeyPath, $privateKey);
                File::put($this->publicKeyPath, $publicKey);
                
                $this->info('Security keys generated successfully');
                
            } catch (\Exception $e) {
                $this->error('Error generating security keys: ' . $e->getMessage());
                Log::error('Error generating security keys: ' . $e->getMessage());
                throw new \Exception('Could not generate security keys: ' . $e->getMessage());
            }
        }
    }
    

    protected function ensureDirectoriesExist(){
        $directories = [
            dirname($this->securityKeyPath),
            storage_path('app/system/keys'),
        ];
        
        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                $this->info("Creating directory: {$directory}");
                
                try {
                    File::makeDirectory($directory, 0755, true);
                } catch (\Exception $e) {
                    $this->error("Error creating directory {$directory}: " . $e->getMessage());
                    Log::error("Error creating directory {$directory}: " . $e->getMessage());
                    throw new \Exception("Could not create required directory {$directory}");
                }
            }
        }
    }
}
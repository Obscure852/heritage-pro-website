<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class LicenseServiceProvider extends ServiceProvider{

    public function register(){}

    public function boot(){
        $this->ensureDirectoriesExist();
    }
    
    protected function ensureDirectoriesExist(){
        if ($this->app->runningInConsole() && !$this->app->runningUnitTests()) {
            return;
        }
        
        $directories = [
            storage_path('app/system'),
            storage_path('app/system/keys'),
        ];
        
        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                try {
                    File::makeDirectory($directory, 0755, true);
                } catch (\Exception $e) {
                    Log::error("Error creating license directory {$directory}: " . $e->getMessage());
                }
            }
        }
    }
}

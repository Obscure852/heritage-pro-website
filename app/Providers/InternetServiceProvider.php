<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;

class InternetServiceProvider extends ServiceProvider{

    public function register(){
        $this->app->singleton('InternetAvailability',function(){
            $response = Http::get('https://google.com');
            return $response->successful();
        });
    }

    public function boot(){}
}

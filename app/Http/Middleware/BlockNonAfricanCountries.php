<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BlockNonAfricanCountries{
    protected $blockedCountries = ['CN'];
    protected $allowedIPs = [
        '127.0.0.1',
        '::1',
        'localhost'
    ];

    public function handle(Request $request, Closure $next){
        $ip = $request->ip();
        if (app()->environment('local') || in_array($ip, $this->allowedIPs)) {
            return $next($request);
        }
        $isBlocked = Cache::remember("ip_blocked_{$ip}", now()->addHours(6), function () use ($ip) {
            try {
                $location = geoip($ip);
                $isBlockedCountry = in_array($location->country_code, $this->blockedCountries);
                Log::info('Geo-blocking check', [
                    'ip' => $ip,
                    'continent' => $location->continent_code,
                    'country' => $location->country,
                    'blocked' => $isBlockedCountry
                ]);

                return $isBlockedCountry;

            } catch (\Exception $e) {
                Log::error('Geo-blocking error', [
                    'ip' => $ip,
                    'error' => $e->getMessage()
                ]);
                return false;
            }
        });
        
        if ($isBlocked) {
            abort(403, 'Access from your location is not allowed.');
        }

        return $next($request);
    }
}

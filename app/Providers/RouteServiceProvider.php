<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/crm';

    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('client-setup-code', function (Request $request) {
            $token = (string) $request->route('token');
            $tokenKey = $token !== '' ? hash('sha256', $token) : 'missing-token';

            return [
                Limit::perMinute(5)->by($request->ip() . '|' . $tokenKey),
                Limit::perHour(20)->by($request->ip()),
            ];
        });

        RateLimiter::for('client-setup-entry', function (Request $request) {
            return [
                Limit::perMinute(30)->by($request->ip()),
                Limit::perHour(120)->by($request->ip()),
            ];
        });

        RateLimiter::for('client-setup-verify', function (Request $request) {
            $token = (string) $request->route('token');
            $tokenKey = $token !== '' ? hash('sha256', $token) : 'missing-token';

            return [
                Limit::perMinute(10)->by($request->ip() . '|' . $tokenKey),
                Limit::perHour(40)->by($request->ip()),
            ];
        });

        RateLimiter::for('client-setup-resume', function (Request $request) {
            $email = strtolower(trim((string) $request->input('email')));

            return [
                Limit::perMinute(3)->by($request->ip() . '|' . hash('sha256', $email)),
                Limit::perHour(10)->by($request->ip()),
            ];
        });
    }
}

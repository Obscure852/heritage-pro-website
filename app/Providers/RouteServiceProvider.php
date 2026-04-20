<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider{
    public const HOME = '/dashboard';

    public function register()
    {
        $this->booted(function () {
            $this->setRootControllerNamespace();

            if ($this->routesAreCached() && $this->app->environment('production')) {
                $this->loadCachedRoutes();
            } else {
                $this->loadRoutes();

                $this->app->booted(function () {
                    $this->app['router']->getRoutes()->refreshNameLookups();
                    $this->app['router']->getRoutes()->refreshActionLookups();
                });
            }
        });
    }

    public function boot(){
        $this->configureRateLimiting();
        
        $this->routes(function () {
            Route::prefix('api')
                ->middleware(['api'])
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    protected function configureRateLimiting(){
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(200)->by(optional($request->user())->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many requests. Please slow down.',
                        'error_code' => 'RATE_LIMIT_EXCEEDED'
                    ], 429);
                });
        });

        RateLimiter::for('public', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many requests from this IP address.',
                        'error_code' => 'RATE_LIMIT_EXCEEDED'
                    ], 429);
                });
        });

        RateLimiter::for('auth', function (Request $request) {
            if (in_array($request->path(), ['api/v1/auth/login', 'api/v1/auth/test', 'api/auth/regional-login', 'api/auth/test-credentials'])) {
                return Limit::perMinute(5)->by($request->ip())
                    ->response(function () {
                        return response()->json([
                            'success' => false,
                            'message' => 'Too many authentication attempts. Please wait before trying again.',
                            'error_code' => 'AUTH_RATE_LIMIT_EXCEEDED'
                        ], 429);
                    });
            }
            
            return $request->user()
                ? Limit::perMinute(150)->by($request->user()->id)
                : Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('export', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(10)->by($request->user()->id)
                : Limit::none();
        });
    }
}

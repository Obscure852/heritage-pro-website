<?php

namespace App\Http;

use App\Http\Middleware\MigrationAuthMiddleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel{

    protected $bootstrappers = [
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        \App\Bootstrap\HandleExceptions::class,
        \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];

    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\persistTerm::class,
        \App\Http\Middleware\BlockNonAfricanCountries::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\EnforceIdleTimeout::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\Localization::class,
           \App\Http\Middleware\persistTerm::class,
           \App\Http\Middleware\ResourceOptimizer::class,
           \App\Http\Middleware\EnsureProfileComplete::class,
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'logUserActivity' => \App\Http\Middleware\LogUserActivity::class,
        'verify.migration.auth' => \App\Http\Middleware\VerifyMigrationCode::class,
        'block.non.african' => \App\Http\Middleware\BlockNonAfricanCountries::class,
        'regional.access' => \App\Http\Middleware\VerifyRegionalAccess::class,
        'api.readonly' => \App\Http\Middleware\ReadOnlyApi::class,
        'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
        'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        'fee.historical-lock' => \App\Http\Middleware\PreventHistoricalFeeModification::class,
        'public.rate_limit' => \App\Http\Middleware\PublicRateLimit::class,
        'channel.enabled' => \App\Http\Middleware\EnsureCommunicationChannelEnabled::class,
        'staff.messages.enabled' => \App\Http\Middleware\EnsureStaffDirectMessagingEnabled::class,
        'staff.presence.enabled' => \App\Http\Middleware\EnsureStaffPresenceLauncherEnabled::class,
    ];
}

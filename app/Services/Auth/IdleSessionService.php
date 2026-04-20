<?php

namespace App\Services\Auth;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdleSessionService
{
    private const SESSION_NAMESPACE = 'idle_timeout.guards';

    public function timeoutMinutes(): int
    {
        $configured = (int) config('session.idle_timeout', config('session.lifetime', 120));

        return max(1, $configured);
    }

    public function timeoutSeconds(): int
    {
        return $this->timeoutMinutes() * 60;
    }

    public function warningSeconds(): int
    {
        $configured = (int) config('session.idle_warning_seconds', 60);
        $maxWarning = max(0, $this->timeoutSeconds() - 1);

        return max(0, min($configured, $maxWarning));
    }

    public function sessionKey(string $guard): string
    {
        return self::SESSION_NAMESPACE . '.' . $guard . '.last_activity_at';
    }

    public function touch(Session $session, string $guard): int
    {
        $timestamp = now()->getTimestamp();
        $session->put($this->sessionKey($guard), $timestamp);

        return $timestamp;
    }

    public function lastActivityTimestamp(Session $session, string $guard): ?int
    {
        $value = $session->get($this->sessionKey($guard));

        return is_numeric($value) ? (int) $value : null;
    }

    public function hasExpired(Session $session, string $guard): bool
    {
        $lastActivity = $this->lastActivityTimestamp($session, $guard);

        if ($lastActivity === null) {
            return false;
        }

        return (now()->getTimestamp() - $lastActivity) >= $this->timeoutSeconds();
    }

    public function loginRouteName(string $guard): string
    {
        return match ($guard) {
            'sponsor' => 'sponsor.login',
            'student' => 'student.login',
            default => 'login',
        };
    }

    public function logoutRouteName(string $guard): string
    {
        return match ($guard) {
            'sponsor' => 'sponsor.logout',
            'student' => 'student.logout',
            default => 'logout',
        };
    }

    public function logoutMethod(string $guard): string
    {
        return $guard === 'web' ? 'POST' : 'GET';
    }

    public function resolveGuard(Request $request): ?string
    {
        $route = $request->route();

        if ($route !== null) {
            foreach ($route->gatherMiddleware() as $middleware) {
                if ($middleware === 'auth:sponsor') {
                    return 'sponsor';
                }

                if ($middleware === 'auth:student') {
                    return 'student';
                }

                if ($middleware === 'auth' || $middleware === 'auth:web') {
                    return 'web';
                }
            }
        }

        return null;
    }

    public function authenticatedGuard(Request $request): ?string
    {
        $preferredGuard = $this->resolveGuard($request);

        if ($preferredGuard !== null && Auth::guard($preferredGuard)->check()) {
            return $preferredGuard;
        }

        foreach (['sponsor', 'student', 'web'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }
}

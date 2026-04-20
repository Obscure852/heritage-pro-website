<?php

namespace App\Http\Middleware;

use App\Services\Auth\IdleSessionService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforceIdleTimeout
{
    public function __construct(
        protected IdleSessionService $idleSession
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        $routeGuard = $this->idleSession->resolveGuard($request);
        $guard = $routeGuard ?? $this->idleSession->authenticatedGuard($request);

        if ($guard === null) {
            return $next($request);
        }

        $session = $request->session();
        $lastActivity = $this->idleSession->lastActivityTimestamp($session, $guard);

        if ($lastActivity === null) {
            return $next($request);
        }

        if ($routeGuard === null && !Auth::guard($guard)->check()) {
            return $next($request);
        }

        if (!$this->idleSession->hasExpired($session, $guard)) {
            return $next($request);
        }

        Auth::guard($guard)->logout();
        $session->invalidate();
        $session->regenerateToken();

        if ($this->shouldReturnJson($request)) {
            return $this->jsonExpiredResponse();
        }

        return $this->redirectExpiredResponse($guard);
    }

    protected function shouldReturnJson(Request $request): bool
    {
        $acceptHeader = strtolower((string) $request->header('Accept', ''));

        return $request->expectsJson()
            || $request->wantsJson()
            || $request->ajax()
            || $request->header('X-Requested-With') === 'XMLHttpRequest'
            || str_contains($acceptHeader, 'application/json')
            || str_contains($acceptHeader, '+json');
    }

    protected function jsonExpiredResponse(): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => 'Your session expired due to inactivity. Please sign in again.',
                'status' => 401,
                'reason' => 'idle_timeout',
            ],
        ], 401);
    }

    protected function redirectExpiredResponse(string $guard): RedirectResponse
    {
        return redirect()
            ->route($this->idleSession->loginRouteName($guard))
            ->with('message', 'Your session expired due to inactivity. Please sign in again.');
    }
}

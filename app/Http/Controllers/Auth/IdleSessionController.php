<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\IdleSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdleSessionController extends Controller
{
    public function __construct(
        protected IdleSessionService $idleSession
    ) {
    }

    public function touchWeb(Request $request): JsonResponse
    {
        return $this->touch($request, 'web');
    }

    public function touchSponsor(Request $request): JsonResponse
    {
        return $this->touch($request, 'sponsor');
    }

    public function touchStudent(Request $request): JsonResponse
    {
        return $this->touch($request, 'student');
    }

    protected function touch(Request $request, string $guard): JsonResponse
    {
        if (!Auth::guard($guard)->check()) {
            return response()->json([
                'error' => [
                    'message' => 'Your session has expired. Please sign in again.',
                    'status' => 401,
                ],
            ], 401);
        }

        $lastActivityAt = $this->idleSession->touch($request->session(), $guard);

        return response()->json([
            'success' => true,
            'last_activity_at' => $lastActivityAt,
            'expires_at' => $lastActivityAt + $this->idleSession->timeoutSeconds(),
            'warning_seconds' => $this->idleSession->warningSeconds(),
        ]);
    }
}

<?php

namespace App\Http\Middleware;

use App\Services\Messaging\StaffMessagingFeatureService;
use Closure;
use Illuminate\Http\Request;

class EnsureStaffDirectMessagingEnabled
{
    public function __construct(
        protected StaffMessagingFeatureService $featureService
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        if ($this->featureService->directMessagesEnabled()) {
            return $next($request);
        }

        $message = 'Staff direct messaging is disabled in Communications Setup.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        return redirect()->back()->with('error', $message);
    }
}

<?php

namespace App\Http\Middleware;

use App\Services\Messaging\CommunicationChannelService;
use Closure;
use Illuminate\Http\Request;

class EnsureCommunicationChannelEnabled
{
    public function __construct(
        protected CommunicationChannelService $channelService
    ) {
    }

    public function handle(Request $request, Closure $next, string $channels)
    {
        $requestedChannels = array_filter(array_map('trim', explode(',', $channels)));

        if ($requestedChannels !== [] && $this->channelService->anyEnabled($requestedChannels)) {
            return $next($request);
        }

        $message = count($requestedChannels) > 1
            ? 'Messaging is disabled in Communications Setup.'
            : strtoupper($requestedChannels[0] ?? 'MESSAGING') . ' is disabled in Communications Setup.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        return redirect()->back()->with('error', $message);
    }
}

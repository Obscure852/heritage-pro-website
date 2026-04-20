<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ReadOnlyApi{
    public function handle(Request $request, Closure $next){
        $allowedMethods = ['GET', 'HEAD', 'OPTIONS'];
        if (!in_array($request->method(), $allowedMethods)) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed',
                'error' => 'This API is read-only. Only GET, HEAD, and OPTIONS methods are allowed.',
                'error_code' => 'METHOD_NOT_ALLOWED',
                'allowed_methods' => $allowedMethods,
                'attempted_method' => $request->method()
            ], 405);
        }
        
        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
        
        $response->headers->set('Content-Security-Policy', "default-src 'self'");
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        $response->headers->set('X-API-Version', 'v1');
        if ($request->user()) {
            $response->headers->set('X-RateLimit-Limit', '60');
        }
        
        return $response;
    }
}

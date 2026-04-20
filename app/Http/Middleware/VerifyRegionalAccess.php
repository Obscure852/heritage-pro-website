<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VerifyRegionalAccess{
    
    public function handle(Request $request, Closure $next){
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $isRegionalAccess = session('is_regional_access');
        $token = $request->bearerToken();
        
        if (!$isRegionalAccess && !$token) {
            return response()->json(['message' => 'Regional access required'], 403);
        }

        if ($token) {
            $validationResult = $this->validateTokenAccess($token, $user);
            
            if (!$validationResult['valid']) {
                return response()->json([
                    'message' => $validationResult['message']
                ], $validationResult['status']);
            }

            $this->logApiAccess($user, $request, $validationResult['abilities']);
        } elseif ($isRegionalAccess) {
            $expiresAt = session('access_expires_at');
            
            if ($expiresAt && Carbon::parse($expiresAt)->isPast()) {
                session()->forget(['is_regional_access', 'regional_token', 'access_expires_at']);
                
                return response()->json([
                    'message' => 'Regional access session expired'
                ], 401);
            }
        }

        return $next($request);
    }

    private function validateTokenAccess(string $token, $user): array{
        $cacheKey = 'token_validation:' . substr($token, 0, 32);
        
        return Cache::remember($cacheKey, 60, function() use ($token, $user) {
            try {
                $tokenParts = explode('|', $token);
                $tokenId = $tokenParts[0] ?? null;
                $tokenValue = $tokenParts[1] ?? $token;
                
                $tokenHash = hash('sha256', $tokenValue);
                
                $accessToken = DB::table('personal_access_tokens')
                    ->where('token', $tokenHash)
                    ->where('tokenable_id', $user->id)
                    ->where('tokenable_type', get_class($user))
                    ->first();

                if (!$accessToken) {
                    return [
                        'valid' => false,
                        'message' => 'Invalid token',
                        'status' => 401
                    ];
                }

                if ($accessToken->expires_at && Carbon::parse($accessToken->expires_at)->isPast()) {
                    DB::table('personal_access_tokens')
                        ->where('id', $accessToken->id)
                        ->delete();
                    
                    return [
                        'valid' => false,
                        'message' => 'Token expired',
                        'status' => 401
                    ];
                }

                $abilities = json_decode($accessToken->abilities, true);
                
                if (!isset($abilities['access-type']) || 
                    !in_array($abilities['access-type'], ['regional_office', 'ministry'])) {
                    return [
                        'valid' => false,
                        'message' => 'Token does not have regional access privileges',
                        'status' => 403
                    ];
                }

                DB::table('personal_access_tokens')
                    ->where('id', $accessToken->id)
                    ->update(['last_used_at' => now()]);

                return [
                    'valid' => true,
                    'abilities' => $abilities,
                    'token_id' => $accessToken->id
                ];
                
            } catch (\Exception $e) {
                Log::error('Token validation error', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id
                ]);
                
                return [
                    'valid' => false,
                    'message' => 'Token validation failed',
                    'status' => 500
                ];
            }
        });
    }

    private function logApiAccess($user, Request $request, array $abilities){
        try {
            $logData = [
                'user_id' => $user->id,
                'school_code' => $abilities['school-code'] ?? 'UNKNOWN',
                'regional_officer_id' => $abilities['regional-officer-id'] ?? null,
                'regional_officer_name' => 'API Access',
                'regional_officer_email' => 'api@regional.gov.bw',
                'access_type' => $abilities['access-type'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'accessed_at' => now(),
                'actions_performed' => json_encode([
                    'endpoint' => $request->path(),
                    'method' => $request->method(),
                    'params' => $request->except(['password', 'token'])
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ];

            DB::table('regional_access_logs')->insertOrIgnore($logData);
            
        } catch (\Exception $e) {
            Log::error('Failed to log API access', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
        }
    }
}

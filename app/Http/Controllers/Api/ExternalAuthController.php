<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExternalAuthController extends Controller{

    private function getTokenAbilities(string $accessType): array{
        switch ($accessType) {
            case 'ministry':
                return [
                    'students.read',
                    'students.export',
                    'admissions.read',
                    'admissions.export',
                    'staff.read',
                    'staff.export',
                    'grades.read',
                    'classes.read',
                    'attendance.read',
                    'assessments.read',
                    'finance.read',
                    'reports.generate',
                    'statistics.view',
                ];
            
            case 'regional_office':
                return [
                    'students.read',
                    'admissions.read',
                    'staff.read',
                    'grades.read',
                    'classes.read',
                    'attendance.read',
                    'statistics.view',
                ];
            
            default:
                return [
                    'students.read',
                    'grades.read',
                    'classes.read',
                ];
        }
    }


    public function authenticateRegionalOffice(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
            'school_code' => 'required|string|max:50',
            'regional_officer' => 'required|array',
            'regional_officer.id' => 'required',
            'regional_officer.name' => 'required|string|max:255',
            'regional_officer.email' => 'required|email',
            'access_type' => 'required|in:regional_office,ministry,standard',
        ]);

        DB::beginTransaction();
        try {
            $user = User::where('email', $request->email)->lockForUpdate()->first();
            if (!$user || !Hash::check($request->password, $user->password)) {
                Log::warning('Failed regional office login attempt', [
                    'email' => $request->email,
                    'school_code' => $request->school_code,
                    'ip' => $request->ip()
                ]);
                
                DB::rollBack();
                sleep(2);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            if (!$user->active || $user->status !== 'Current') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'User account is inactive'
                ], 403);
            }
            
            $expirationHours = match($request->access_type) {
                'ministry' => 24,
                'regional_office' => 8,
                default => 4
            };
            $expiresAt = Carbon::now()->addHours($expirationHours);
            $abilities = $this->getTokenAbilities($request->access_type);
            $tokenName = sprintf('%s-access-%s', 
                $request->access_type, 
                Carbon::now()->format('Y-m-d-H:i:s')
            );
            
            $tokenResult = $user->createToken($tokenName, $abilities);
            $token = $tokenResult->plainTextToken;
            
            DB::table('personal_access_tokens')->where('id', $tokenResult->accessToken->id)->update([
                    'expires_at' => $expiresAt,
                    'abilities' => json_encode($abilities)
                ]);

            DB::table('regional_access_logs')->insert([
                'user_id' => $user->id,
                'school_code' => $request->school_code,
                'regional_officer_id' => $request->regional_officer['id'],
                'regional_officer_name' => $request->regional_officer['name'],
                'regional_officer_email' => $request->regional_officer['email'],
                'access_type' => $request->access_type,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'accessed_at' => now(),
                'created_at' => now()
            ]);

            $loginUrl = $this->generateAutoLoginUrl($token);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Authentication successful',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'login_url' => $loginUrl,
                    'abilities' => $abilities,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'position' => $user->position,
                    ],
                    'expires_at' => $expiresAt->toIso8601String(),
                    'expires_in_hours' => $expirationHours
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Regional office authentication error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed'
            ], 500);
        }
    }

    public function autoLogin(Request $request){
        $token = urldecode($request->get('token'));
        
        $tokenParts = explode('|', $token);
        if (count($tokenParts) !== 2) {
            return redirect('/login')->with('error', 'Invalid token format');
        }
        
        $tokenId = $tokenParts[0];
        $tokenHash = hash('sha256', $tokenParts[1]);
        $personalAccessToken = DB::table('personal_access_tokens')->where('id', $tokenId)->where('token', $tokenHash)->first();
        
        if (!$personalAccessToken) {
            return redirect('/login')->with('error', 'Token not found');
        }
        
        if ($personalAccessToken->expires_at && Carbon::parse($personalAccessToken->expires_at)->isPast()) {
            return redirect('/login')->with('error', 'Token has expired');
        }
        
        $user = User::find($personalAccessToken->tokenable_id);
        if (!$user) {
            return redirect('/login')->with('error', 'User not found');
        }
        
        Auth::loginUsingId($user->id);
        session()->save();
        DB::table('personal_access_tokens')->where('id', $tokenId)->update(['last_used_at' => now()]);
        return redirect()->route('dashboard')->with('success', 'Logged in via Regional Office');
    }

    public function validateToken(Request $request){
        $request->validate([
            'token' => 'required|string'
        ]);

        try {
            $tokenParts = explode('|', $request->token);
            if (count($tokenParts) !== 2) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid token format'
                ], 401);
            }
            
            $tokenId = $tokenParts[0];
            $tokenHash = hash('sha256', $tokenParts[1]);
            
            $personalAccessToken = DB::table('personal_access_tokens')
                ->where('id', $tokenId)
                ->where('token', $tokenHash)
                ->first();

            if (!$personalAccessToken) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Token not found'
                ], 401);
            }
            
            if ($personalAccessToken->expires_at && Carbon::parse($personalAccessToken->expires_at)->isPast()) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Token has expired'
                ], 401);
            }

            $user = User::find($personalAccessToken->tokenable_id);
            $abilities = json_decode($personalAccessToken->abilities, true) ?? [];

            return response()->json([
                'valid' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email
                ],
                'abilities' => $abilities,
                'expires_at' => $personalAccessToken->expires_at
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Token validation failed'
            ], 500);
        }
    }

    public function revokeAccess(Request $request){
        $request->validate([
            'token' => 'required|string'
        ]);

        try {
            $tokenParts = explode('|', $request->token);
            if (count($tokenParts) !== 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token format'
                ], 400);
            }
            
            $tokenId = $tokenParts[0];
            $tokenHash = hash('sha256', $tokenParts[1]);
            
            $deleted = DB::table('personal_access_tokens')->where('id', $tokenId)->where('token', $tokenHash)->delete();
            if ($deleted === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not found'
                ], 404);
            }

            Log::info('Access token revoked', [
                'token_id' => $tokenId,
                'revoked_by' => $request->user()->id ?? 'system',
                'revoked_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Access revoked successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke access'
            ], 500);
        }
    }

    public function testCredentials(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        try {
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No user found with this email address',
                    'user_exists' => false
                ]);
            }
            
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is incorrect',
                    'user_exists' => true,
                    'password_valid' => false
                ]);
            }
            
            if (!$user->active || $user->status !== 'Current') {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is inactive',
                    'user_exists' => true,
                    'password_valid' => true,
                    'account_active' => false,
                    'user_status' => $user->status
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Credentials are valid',
                'user_exists' => true,
                'password_valid' => true,
                'account_active' => true,
                'user_details' => [
                    'name' => $user->full_name,
                    'position' => $user->position,
                    'department' => $user->department,
                    'status' => $user->status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Credential test error', [
                'error' => $e->getMessage(),
                'email' => $request->email
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to test credentials'
            ], 500);
        }
    }

    private function generateAutoLoginUrl(string $token): string{
        return route('auth.auto-login', [
            'token' => urlencode($token),
            'expires' => now()->addHours(1)->timestamp,
        ], true);
    }
}

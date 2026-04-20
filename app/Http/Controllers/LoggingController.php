<?php

namespace App\Http\Controllers;

use App\Models\BackupLog;
use App\Models\Logging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class LoggingController extends Controller{

    public function index(Request $request){
        $logsPerPage = 2000;
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $logsQuery = Logging::with('user:id,firstname,lastname')
            ->select('id', 'location', 'user_id', 'ip_address', 'url', 'method', 'created_at', 'changes')
            ->orderBy('created_at', 'desc');
        
        $backupLogsQuery = BackupLog::select('id', 'database_name', 'file_path', 'file_size', 'backup_time', 'status')->orderBy('backup_time', 'desc');
        
        $tokens = $user ? $user->tokens()
            ->select(['id', 'name', 'created_at', 'last_used_at'])
            ->orderBy('created_at', 'desc')
            ->get() : collect();
        
        if ($request->filled('search')) {
            $search = $request->input('search');
            $logsQuery->where(function ($query) use ($search) {
                $query->where('location', 'like', "%$search%")
                    ->orWhere('ip_address', 'like', "%$search%")
                    ->orWhere('url', 'like', "%$search%")
                    ->orWhere('method', 'like', "%$search%")
                    ->orWhere('changes', 'like', "%$search%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where(DB::raw("CONCAT(firstname, ' ', lastname)"), 'like', "%$search%");
                    });
            });
        }
        
        if ($request->filled('date_from')) {
            $logsQuery->where('created_at', '>=', $request->input('date_from'));
            $backupLogsQuery->where('backup_time', '>=', $request->input('date_from'));
        }
        
        if ($request->filled('date_to')) {
            $logsQuery->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');
            $backupLogsQuery->where('backup_time', '<=', $request->input('date_to') . ' 23:59:59');
        }
        
        $logs = $logsQuery->paginate($logsPerPage);
        
        $logs->getCollection()->transform(function ($log) {
            $changes = json_decode($log->changes, true) ?? [];
            $action = $changes['action'] ?? '';
            
            $getDataSummary = function($data, $action) {
                if (!is_array($data)) {
                    return [];
                }

                if ($action === 'Marks Saved') {
                    return array_values(array_filter($data['summary_badges'] ?? [], fn ($badge) => is_string($badge) && trim($badge) !== ''));
                }
                
                if (strtolower($action) === 'delete' || strtolower($action) === 'deleted') {
                    $summary = [];
                    foreach ($data as $key => $value) {
                        if (in_array($key, ['user_type', 'user_id', 'email'])) {
                            continue;
                        }
                        
                        if (is_array($value)) {
                            $summary[$key] = '[' . implode(', ', $value) . ']';
                        } elseif (is_null($value)) {
                            $summary[$key] = 'null';
                        } elseif (is_bool($value)) {
                            $summary[$key] = $value ? 'true' : 'false';
                        } else {
                            $summary[$key] = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
                        }
                    }
                    return $summary;
                }
                return array_diff(array_keys($data), ['user_type', 'user_id', 'email']);
            };

            $data = is_array($changes['data'] ?? null) ? $changes['data'] : [];

            $log->changes = [
                'action' => $action,
                'data_summary' => $getDataSummary($data, $action),
                'data_summary_mode' => $action === 'Marks Saved' ? 'labels' : 'keys',
                'user_type' => $data['user_type'] ?? $changes['user_type'] ?? null,
                'non_user_id' => $data['user_id'] ?? $changes['user_id'] ?? null,
                'non_user_email' => $data['email'] ?? $changes['email'] ?? null,
            ];
            return $log;
        });
        
        $backupLogs = $backupLogsQuery->paginate($logsPerPage);
        return view('settings.index', [
            'logs' => $logs,
            'backupLogs' => $backupLogs,
            'search' => $request->input('search'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'tokens' => $tokens,
        ]);
    }

    public function clearOldLogs(Request $request){
        $request->validate([
            'date' => 'required|date|before_or_equal:today',
        ]);
    
        $date = $request->input('date');
    
        $cutoffTime = Carbon::parse($date)->isToday() 
            ? Carbon::now() 
            : Carbon::parse($date)->endOfDay();
    
        try {
            // Set a longer timeout for this operation
            set_time_limit(300); // 5 minutes
            
            DB::beginTransaction();
            $deletedCount = 0;
            $deletedBackupCount = 0;
            
            Logging::where('created_at', '<=', $cutoffTime)
                ->chunkById(200, function ($logs) use (&$deletedCount) {
                    $deletedCount += $logs->count();
                    $logs->each(function ($log) {
                        $log->delete();
                    });
                    
                    usleep(10000);
                });
            
            BackupLog::where('backup_time', '<=', $cutoffTime)
                ->chunkById(200, function ($backupLogs) use (&$deletedBackupCount) {
                    $deletedBackupCount += $backupLogs->count();
                    $backupLogs->each(function ($backupLog) {
                        $backupLog->delete();
                    });
                    
                    usleep(10000);
                });
    
            DB::commit();
    
            $message = "Successfully deleted {$deletedCount} logs";
            if ($deletedBackupCount > 0) {
                $message .= " and {$deletedBackupCount} backup logs";
            }
            $message .= " up to " . $cutoffTime->format('Y-m-d H:i:s') . ".";
    
            return redirect()->route('logs.index')->with('message', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to clear old logs: " . $e->getMessage());
            return redirect()->route('logs.index')->with('error', 'Failed to clear old logs. Please try again.');
        }
    }


    public function store(Request $request){
        $request->validate([
            'token_name' => 'required|string|max:255',
            'abilities' => 'nullable|array',
            'abilities.*' => 'string|max:100',
            'grant_all' => 'nullable|boolean'
        ]);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return back()->with('error', 'User not authenticated');
        }

        try {
            $allSystemAbilities = [
                'students.read',
                'admissions.read',
                'staff.read'
            ];

            $requestedAbilities = $request->input('abilities', []);
            $grantAll = $request->input('grant_all', false);
            
            if ($grantAll || empty($requestedAbilities)) {
                $finalAbilities = $allSystemAbilities;
                Log::info('Granting all system abilities to token: ' . $request->token_name);
            } else {
                $finalAbilities = array_values(array_unique(
                    array_intersect($requestedAbilities, $allSystemAbilities)
                ));
                
                if (empty($finalAbilities)) {
                    $finalAbilities = [
                        'students.read',
                        'admissions.read',
                        'staff.read'
                    ];
                    Log::warning('No valid abilities specified, granting default read-only access');
                }
            }

            $token = $user->createToken($request->token_name, $finalAbilities);
            Log::info('API Token created', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'token_name' => $request->token_name,
                'token_id' => $token->accessToken->id,
                'abilities_count' => count($finalAbilities),
                'abilities' => $finalAbilities,
                'created_at' => now()
            ]);

            session()->flash('token_info', [
                'token' => $token->plainTextToken,
                'name' => $request->token_name,
                'abilities' => $finalAbilities,
                'abilities_count' => count($finalAbilities),
                'created_at' => now()->format('Y-m-d H:i:s')
            ]);
            
            return back()->with([
                'token' => $token->plainTextToken,
                'success' => sprintf(
                    'Token "%s" generated successfully with %d abilities. This token has %s access.',
                    $request->token_name,
                    count($finalAbilities),
                    $grantAll ? 'FULL SYSTEM' : 'CUSTOM'
                )
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create token', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id
            ]);
            
            return back()->with('error', 'Failed to generate token: ' . $e->getMessage());
        }
    }

    public function getAvailableAbilities(){
        return [
            'Student Management' => [
                'students.read' => 'View student records'
            ],
            'Admissions' => [
                'admissions.read' => 'View admissions'
            ],
            'Staff Management' => [
                'staff.read' => 'View staff records'
            ]
        ];
    }

    public function destroy($id){
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return back()->with('error', 'User not authenticated');
        }

        try {
            $user->tokens()->where('id', $id)->delete();
            return back()->with('success', 'Token revoked successfully');
        } catch (\Exception $e) {
            Log::error('Failed to revoke token: ' . $e->getMessage());
            return back()->with('error', 'Failed to revoke token. Please try again.');
        }
    }

    public function tutorials(){
        return view('video.ui-video');
    }

}

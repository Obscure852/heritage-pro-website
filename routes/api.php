<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\AdmissionApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\ExternalAuthController;
use App\Http\Controllers\SmsWebhookController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Http\Controllers\StaffAttendance\WebhookController as AttendanceWebhookController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\SchoolSetupApiController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API routes are stateless and use token authentication.
| All data-access routes are READ-ONLY to ensure data integrity.
|
*/

Route::prefix('auth')->group(function(){
    Route::post('/regional-login', [ExternalAuthController::class, 'authenticateRegionalOffice'])->name('api.auth.regional-login');
    Route::post('/test-credentials', [ExternalAuthController::class, 'testCredentials'])->name('api.auth.test-credentials');
    Route::post('/validate-token', [ExternalAuthController::class, 'validateToken'])->name('api.auth.validate-token');
});

Route::middleware(['auth:sanctum'])->group(function(){
    Route::middleware(['throttle:api'])->group(function(){
        
        // Students endpoints
        Route::prefix('students')->group(function(){
            Route::get('/', [StudentApiController::class, 'index'])
                ->middleware('ability:students.read')
                ->name('api.students.index');
            
            Route::get('/statistics', [StudentApiController::class, 'statistics'])
                ->middleware('ability:statistics.view,students.read')
                ->name('api.students.statistics');
            
            // Total endpoints with term filtering support
            Route::get('/totals/all', [StudentApiController::class, 'totals'])->name('api.students.totals');
            Route::get('/totals/by-gender', [StudentApiController::class, 'totalsByGender'])->name('api.students.totals.gender');
            Route::get('/totals/by-status', [StudentApiController::class, 'totalsByStatus'])->name('api.students.totals.status');
            Route::get('/totals/by-grade', [StudentApiController::class, 'totalsByGrade'])->name('api.students.totals.grade');
            Route::get('/totals/summary', [StudentApiController::class, 'summary'])->name('api.students.summary');
            Route::get('/totals/school', [StudentApiController::class, 'schoolTotals'])->name('api.students.totals.school');
            
            // Available terms endpoint
            Route::get('/available-terms', [StudentApiController::class, 'availableTerms'])->name('api.students.available-terms');
            
            Route::get('/{id}', [StudentApiController::class, 'show'])
                ->where('id', '[0-9]+')
                ->middleware('ability:students.read')
                ->name('api.students.show');
        });

        Route::prefix('staff')->group(function(){
            Route::get('/', [UserApiController::class, 'index'])
                ->middleware('ability:staff.read')
                ->name('api.staff.index');
            
            Route::get('/statistics', [UserApiController::class, 'statistics'])
                ->middleware('ability:staff.read')
                ->name('api.staff.statistics');
            
            Route::get('/totals/all', [UserApiController::class, 'totals'])
                ->middleware('ability:staff.read')
                ->name('api.staff.totals');
            
            Route::get('/totals/by-gender', [UserApiController::class, 'totalsByGender'])
                ->middleware('ability:staff.read')
                ->name('api.staff.totals.gender');
            
            Route::get('/{id}', [UserApiController::class, 'show'])
                ->where('id', '[0-9]+')
                ->middleware('ability:staff.read')
                ->name('api.staff.show');
        });

        // School endpoints
        Route::prefix('school')->group(function(){
            Route::get('/info', [SchoolSetupApiController::class, 'getSchoolInfo'])
                ->middleware('ability:staff.read')
                ->name('api.school.info');
            
            Route::get('/logo', [SchoolSetupApiController::class, 'getSchoolLogo'])
                ->middleware('ability:staff.read')
                ->name('api.school.logo');
            
            Route::get('/facilities', [SchoolSetupApiController::class, 'getFacilities'])
                ->middleware('ability:staff.read')
                ->name('api.school.facilities');
            
            Route::get('/qualified-teachers', [SchoolSetupApiController::class, 'getQualifiedTeachers'])
                ->middleware('ability:staff.read')
                ->name('api.school.qualified-teachers');
        });

        // Admissions endpoints
        Route::prefix('admissions')->group(function(){
            Route::get('/', [AdmissionApiController::class, 'index'])
                ->middleware('ability:admissions.read')
                ->name('api.admissions.index');
            
            Route::get('/statistics', [AdmissionApiController::class, 'statistics'])
                ->middleware('ability:statistics.view,admissions.read')
                ->name('api.admissions.statistics');
            
            Route::get('/{id}', [AdmissionApiController::class, 'show'])
                ->middleware('ability:admissions.read')
                ->name('api.admissions.show');
        });
    });
    
    Route::post('/auth/revoke-access', [ExternalAuthController::class, 'revokeAccess'])->name('api.auth.revoke-access');
});

Route::get('/health', function(){
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'version' => config('app.version', '1.0.0'),
        'api_version' => 'v1',
        'environment' => app()->environment(),
    ]);
})->name('api.health');

/*
|--------------------------------------------------------------------------
| SMS Webhook Routes (Public - Called by Link SMS)
|--------------------------------------------------------------------------
*/
Route::prefix('webhooks/sms')->group(function () {
    // Delivery status callback from Link SMS
    Route::post('/delivery-status', [SmsWebhookController::class, 'handleDeliveryStatus'])
        ->name('api.webhooks.sms.delivery-status');

    // Health check for webhook endpoint
    Route::get('/health', [SmsWebhookController::class, 'healthCheck'])
        ->name('api.webhooks.sms.health');
});

Route::prefix('webhooks/whatsapp')->group(function () {
    Route::post('/status', [WhatsAppWebhookController::class, 'handleStatus'])
        ->name('api.webhooks.whatsapp.status');

    Route::post('/inbound', [WhatsAppWebhookController::class, 'handleInbound'])
        ->name('api.webhooks.whatsapp.inbound');
});

/*
|--------------------------------------------------------------------------
| Biometric Attendance Webhook Routes
|--------------------------------------------------------------------------
|
| Endpoints for receiving attendance events from biometric devices.
| Two connectivity modes:
| - Push: Device (e.g., Hikvision) pushes events directly (no auth required,
|         but uses webhook secret for signature verification)
| - Agent: On-premise sync agent pushes events (requires Sanctum auth)
|
*/
Route::prefix('attendance/webhook')->group(function () {
    // Hikvision push mode - device pushes events directly
    // No auth middleware - uses webhook secret for verification
    Route::post('/hikvision/{device}', [AttendanceWebhookController::class, 'hikvision'])
        ->name('api.attendance.webhook.hikvision');

    // Agent mode - on-premise agent pushes events
    // Requires Sanctum authentication
    Route::post('/agent/{device}', [AttendanceWebhookController::class, 'agent'])
        ->middleware('auth:sanctum')
        ->name('api.attendance.webhook.agent');
});

// Delivery stats endpoint (requires auth)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/sms/delivery-stats', [SmsWebhookController::class, 'getStats'])
        ->name('api.sms.delivery-stats');
});

Route::get('/', function(){
    return response()->json([
        'name' => 'School Management System API',
        'version' => 'v1',
        'description' => 'Read-only API for accessing school management data',
        'documentation' => url('/api/documentation'),
        'health_check' => route('api.health'),
        'authentication' => [
            'type' => 'Bearer Token',
            'header' => 'Authorization: Bearer {token}',
            'obtain_token' => 'Contact system administrator'
        ],
        'rate_limits' => [
            'authenticated' => '60 requests per minute',
            'auth_endpoints' => '10 requests per minute',
        ],
        'endpoints' => [
            'students' => [
                'list' => '/api/students',
                'totals' => '/api/students/totals/all',
                'school_totals' => '/api/students/totals/school'
            ],
            'staff' => [
                'list' => '/api/staff',
                'totals' => '/api/staff/totals/all'
            ],
            'school' => [
                'info' => '/api/school/info',
                'logo' => '/api/school/logo',
                'facilities' => '/api/school/facilities',
                'qualified_teachers' => '/api/school/qualified-teachers'
            ],
            'admissions' => [
                'list' => '/api/admissions',
                'statistics' => '/api/admissions/statistics'
            ]
        ],
        'contact' => [
            'email' => config('mail.from.address'),
            'support' => config('app.support_url')
        ]
    ]);
})->name('api.info')->middleware('throttle:60,1');

Route::fallback(function(){
    return response()->json([
        'success' => false,
        'message' => 'Endpoint not found',
        'error' => 'The requested API endpoint does not exist'
    ], 404);
});

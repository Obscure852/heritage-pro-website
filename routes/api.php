<?php

use App\Http\Controllers\Api\Crm\BiometricController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->prefix('crm/attendance')->group(function () {
    Route::post('/biometric-event', [BiometricController::class, 'event'])->name('api.crm.attendance.biometric-event');
    Route::post('/biometric-heartbeat', [BiometricController::class, 'heartbeat'])->name('api.crm.attendance.biometric-heartbeat');
});

// ZKTeco ADMS protocol endpoints (device-initiated, no Sanctum — authenticated by serial number + communication key)
Route::prefix('crm/attendance/iclock')->group(function () {
    Route::match(['GET', 'POST'], '/cdata', [BiometricController::class, 'iclockCdata'])->name('api.crm.attendance.iclock.cdata');
    Route::get('/getrequest', [BiometricController::class, 'iclockGetRequest'])->name('api.crm.attendance.iclock.getrequest');
    Route::post('/devicecmd', [BiometricController::class, 'iclockDeviceCmd'])->name('api.crm.attendance.iclock.devicecmd');
});

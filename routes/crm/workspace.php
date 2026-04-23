<?php

use App\Http\Controllers\Crm\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::get('/search', [WorkspaceController::class, 'search'])->name('search');
Route::get('/presence/launcher', [WorkspaceController::class, 'presenceLauncher'])->name('presence.launcher');
Route::post('/presence/heartbeat', [WorkspaceController::class, 'presenceHeartbeat'])->name('presence.heartbeat');
Route::get('/presence/unread-count', [WorkspaceController::class, 'presenceUnreadCount'])->name('presence.unread-count');
Route::patch('/presence/discussion-sound', [WorkspaceController::class, 'updateDiscussionSoundPreference'])->name('presence.discussion-sound.update');

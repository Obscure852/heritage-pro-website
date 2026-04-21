<?php

use App\Http\Controllers\Crm\DiscussionController;
use Illuminate\Support\Facades\Route;

Route::get('/discussions', [DiscussionController::class, 'index'])->name('discussions.index');
Route::get('/discussions/create', [DiscussionController::class, 'create'])->name('discussions.create');
Route::post('/discussions', [DiscussionController::class, 'store'])->name('discussions.store');
Route::get('/discussions/{discussionThread}', [DiscussionController::class, 'show'])->name('discussions.show');
Route::delete('/discussions/{discussionThread}', [DiscussionController::class, 'destroy'])->name('discussions.destroy');
Route::post('/discussions/{discussionThread}/messages', [DiscussionController::class, 'storeMessage'])->name('discussions.messages.store');

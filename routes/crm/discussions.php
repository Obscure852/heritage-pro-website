<?php

use App\Http\Controllers\Crm\AppDiscussionController;
use App\Http\Controllers\Crm\DiscussionController;
use App\Http\Controllers\Crm\EmailDiscussionController;
use App\Http\Controllers\Crm\WhatsappDiscussionController;
use Illuminate\Support\Facades\Route;

Route::prefix('/discussions/app')->name('discussions.app.')->group(function () {
    Route::get('/', [AppDiscussionController::class, 'workspace'])->name('workspace');
    Route::get('/company-chat', [AppDiscussionController::class, 'companyChat'])->name('company-chat');
    Route::post('/company-chat/messages', [AppDiscussionController::class, 'storeCompanyChatMessage'])->name('company-chat.messages.store');

    Route::get('/direct/create', [AppDiscussionController::class, 'createDirect'])->name('direct.create');
    Route::post('/direct', [AppDiscussionController::class, 'storeDirect'])->name('direct.store');
    Route::get('/direct/start', [AppDiscussionController::class, 'startDirect'])->name('direct.start');
    Route::get('/direct/{discussionThread}/edit', [AppDiscussionController::class, 'editDirect'])->name('direct.edit');
    Route::match(['put', 'patch'], '/direct/{discussionThread}', [AppDiscussionController::class, 'updateDirect'])->name('direct.update');
    Route::post('/direct/{discussionThread}/messages', [AppDiscussionController::class, 'storeDirectMessage'])->name('direct.messages.store');

    Route::get('/bulk/create', [AppDiscussionController::class, 'createBulk'])->name('bulk.create');
    Route::post('/bulk', [AppDiscussionController::class, 'storeBulk'])->name('bulk.store');
    Route::get('/bulk/{discussionCampaign}/edit', [AppDiscussionController::class, 'editBulk'])->name('bulk.edit');
    Route::match(['put', 'patch'], '/bulk/{discussionCampaign}', [AppDiscussionController::class, 'updateBulk'])->name('bulk.update');
    Route::post('/bulk/{discussionCampaign}/send', [AppDiscussionController::class, 'sendBulk'])->name('bulk.send');

    Route::get('/threads/{discussionThread}', [AppDiscussionController::class, 'workspace'])->name('threads.show');
    Route::get('/threads/{discussionThread}/poll', [AppDiscussionController::class, 'poll'])->name('threads.poll');

    Route::get('/attachments/{attachment}/preview', [AppDiscussionController::class, 'previewAttachment'])->name('attachments.preview');
    Route::get('/attachments/{attachment}/open', [AppDiscussionController::class, 'openAttachment'])->name('attachments.open');
    Route::get('/attachments/{attachment}/download', [AppDiscussionController::class, 'downloadAttachment'])->name('attachments.download');
});

Route::prefix('/discussions/email')->name('discussions.email.')->group(function () {
    Route::get('/', [EmailDiscussionController::class, 'index'])->name('index');
    Route::get('/direct/create', [EmailDiscussionController::class, 'createDirect'])->name('direct.create');
    Route::post('/direct', [EmailDiscussionController::class, 'storeDirect'])->name('direct.store');
    Route::get('/direct/{discussionThread}', [EmailDiscussionController::class, 'showDirect'])->name('direct.show');
    Route::get('/direct/{discussionThread}/edit', [EmailDiscussionController::class, 'editDirect'])->name('direct.edit');
    Route::match(['put', 'patch'], '/direct/{discussionThread}', [EmailDiscussionController::class, 'updateDirect'])->name('direct.update');
    Route::post('/direct/{discussionThread}/send', [EmailDiscussionController::class, 'sendDirect'])->name('direct.send');
    Route::post('/direct/{discussionThread}/reply', [EmailDiscussionController::class, 'replyDirect'])->name('direct.reply');

    Route::get('/bulk/create', [EmailDiscussionController::class, 'createBulk'])->name('bulk.create');
    Route::post('/bulk', [EmailDiscussionController::class, 'storeBulk'])->name('bulk.store');
    Route::get('/bulk/{discussionCampaign}/edit', [EmailDiscussionController::class, 'editBulk'])->name('bulk.edit');
    Route::match(['put', 'patch'], '/bulk/{discussionCampaign}', [EmailDiscussionController::class, 'updateBulk'])->name('bulk.update');
    Route::post('/bulk/{discussionCampaign}/send', [EmailDiscussionController::class, 'sendBulk'])->name('bulk.send');
});

Route::prefix('/discussions/whatsapp')->name('discussions.whatsapp.')->group(function () {
    Route::get('/', [WhatsappDiscussionController::class, 'index'])->name('index');
    Route::get('/direct/create', [WhatsappDiscussionController::class, 'createDirect'])->name('direct.create');
    Route::post('/direct', [WhatsappDiscussionController::class, 'storeDirect'])->name('direct.store');
    Route::get('/direct/{discussionThread}', [WhatsappDiscussionController::class, 'showDirect'])->name('direct.show');
    Route::get('/direct/{discussionThread}/edit', [WhatsappDiscussionController::class, 'editDirect'])->name('direct.edit');
    Route::match(['put', 'patch'], '/direct/{discussionThread}', [WhatsappDiscussionController::class, 'updateDirect'])->name('direct.update');
    Route::post('/direct/{discussionThread}/send', [WhatsappDiscussionController::class, 'sendDirect'])->name('direct.send');
    Route::post('/direct/{discussionThread}/reply', [WhatsappDiscussionController::class, 'replyDirect'])->name('direct.reply');

    Route::get('/bulk/create', [WhatsappDiscussionController::class, 'createBulk'])->name('bulk.create');
    Route::post('/bulk', [WhatsappDiscussionController::class, 'storeBulk'])->name('bulk.store');
    Route::get('/bulk/{discussionCampaign}/edit', [WhatsappDiscussionController::class, 'editBulk'])->name('bulk.edit');
    Route::match(['put', 'patch'], '/bulk/{discussionCampaign}', [WhatsappDiscussionController::class, 'updateBulk'])->name('bulk.update');
    Route::post('/bulk/{discussionCampaign}/send', [WhatsappDiscussionController::class, 'sendBulk'])->name('bulk.send');
});

Route::get('/discussions', [DiscussionController::class, 'index'])->name('discussions.index');
Route::get('/discussions/create', [DiscussionController::class, 'create'])->name('discussions.create');
Route::post('/discussions', [DiscussionController::class, 'store'])->name('discussions.store');
Route::post('/discussions/{discussionThread}/messages', [DiscussionController::class, 'storeMessage'])->name('discussions.messages.store');
Route::delete('/discussions/{discussionThread}', [DiscussionController::class, 'destroy'])->name('discussions.destroy');
Route::get('/discussions/{discussionThread}', [DiscussionController::class, 'show'])->name('discussions.show');

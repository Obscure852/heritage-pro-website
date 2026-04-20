<?php
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\NotificationAnalyticsController;
use App\Http\Controllers\SmsTemplateController;
use App\Http\Controllers\WhatsappTemplateController;
use Illuminate\Support\Facades\Route;

Route::prefix('notifications')->middleware(['auth','throttle:auth','block.non.african','can:access-communications'])->group(function () {
  Route::get('/bulk/messaging', [NotificationController::class, 'bulkMessaging'])->middleware('channel.enabled:sms,whatsapp')->name('notifications.bulk-sms-index');
  Route::get('/bulk/mailing', [NotificationController::class, 'bulkMailing'])->name('notifications.bulk-mail-index');


  Route::post('/send-bulk-sms', [NotificationController::class, 'sendBulkSmsWithDatabase'])->middleware('channel.enabled:sms')->name('notifications.send-bulk-sms');
  Route::post('/send-bulk-message', [NotificationController::class, 'sendBulkMessage'])->middleware('channel.enabled:sms,whatsapp')->name('notifications.send-bulk-message');
  Route::get('/job-progress/{jobId}', [NotificationController::class, 'getJobProgressFromDatabase'])->name('notifications.job-progress');
  Route::post('/job-cancel/{jobId}', [NotificationController::class, 'cancelJob'])->name('notifications.job-cancel');

  Route::get('/sms-job-history', [NotificationController::class, 'getSmsJobHistory'])->middleware('channel.enabled:sms')->name('notifications.sms-job-history');
  Route::get('/sms-delivery-log', [NotificationController::class, 'getSmsJobHistory'])->name('sms.delivery-log');
  Route::post('/send-email', [NotificationController::class, 'sendEmail'])->name('notifications.send-email');
  Route::post('/bulk/send-email', [NotificationController::class, 'sendBulkEmail'])->name('notifications.send-bulk-email');

  Route::post('/bulk/email/recipients', [NotificationController::class, 'checkEmailRecipients'])->name('notifications.check-email-recipients');
  Route::post('/bulk/sms/recipients', [NotificationController::class, 'checkSMSRecipients'])->middleware('channel.enabled:sms')->name('notifications.check-sms-recipients');
  Route::post('/bulk/whatsapp/recipients', [NotificationController::class, 'checkWhatsAppRecipients'])->middleware('channel.enabled:whatsapp')->name('notifications.check-whatsapp-recipients');

  Route::post('/bulk/create', [NotificationController::class, 'saveSmsApi'])->name('notifications.store-api-settings');
  Route::get('/bulk/messages', [NotificationController::class, 'getMessages'])->name('notifications.get-messages');
  Route::get('/bulk/emails', [NotificationController::class, 'getEmails'])->name('notifications.get-emails');
  Route::get('/bulk/notificatons', [NotificationController::class, 'getNotifications'])->name('notifications.get-getNotifications');

  Route::get('/staff/create', [NotificationController::class, 'staffNotification'])->name('notifications.staff-create');
  Route::get('/sponsors/create', [NotificationController::class, 'sponsorNotification'])->name('notifications.sponsors-create');

  Route::get('/details/{id}', [NotificationController::class, 'notificationDetails'])->name('notification.details');
  Route::get('/staff/edit/{id}', [NotificationController::class, 'editNotification'])->name('notifications.edit-notification');
  Route::get('/sponsor/edit/{id}', [NotificationController::class, 'editSponsorNotification'])->name('notifications.edit-sponsor-notification');

  Route::post('/staff/store', [NotificationController::class, 'store'])->name('notifications.store');
  Route::post('/sponsors/store', [NotificationController::class, 'storeSponsorNotification'])->name('notifications.store-sponsors-notification');

  Route::post('/staff/update/{id}', [NotificationController::class, 'update'])->name('notifications.notification-update');
  Route::post('/sponsors/update/{id}', [NotificationController::class, 'updateSponsorNotification'])->name('notifications.update-sponsor-notification');
  Route::post('/notification/{id}/toggle-pin', [NotificationController::class, 'togglePin'])->name('notification.toggle-pin');
  Route::get('/notification/delete/{id}', [NotificationController::class, 'deleteNotification'])->name('notification.delete-notification');

  Route::get('/download-attachment/{id}', [NotificationController::class, 'download'])->name('notification.download-attachment');
  Route::get('/destroy-attachment/{notificationId}/{attachmentId}', [NotificationController::class, 'destroyAttachment'])->name('notification.destroy-attachment');

  Route::get('/comment/delete/{id}', [NotificationController::class, 'deleteComment'])->name('notification.delete-comment');
  Route::post('/comment/store', [NotificationController::class, 'notificationComment'])->name('notifications.notification-comment');
  Route::post('/{notification}/comment', [NotificationController::class, 'comment'])->name('notifications.comment');

  Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');

  // Template Management Routes
  Route::prefix('templates')->group(function () {
    Route::get('/', [NotificationTemplateController::class, 'index'])->name('templates.index');
    Route::get('/create', [NotificationTemplateController::class, 'create'])->name('templates.create');
    Route::post('/', [NotificationTemplateController::class, 'store'])->name('templates.store');
    Route::get('/{template}', [NotificationTemplateController::class, 'show'])->name('templates.show');
    Route::get('/{template}/edit', [NotificationTemplateController::class, 'edit'])->name('templates.edit');
    Route::put('/{template}', [NotificationTemplateController::class, 'update'])->name('templates.update');
    Route::delete('/{template}', [NotificationTemplateController::class, 'destroy'])->name('templates.destroy');
    Route::post('/{template}/toggle-status', [NotificationTemplateController::class, 'toggleStatus'])->name('templates.toggle-status');

    // AJAX endpoint for fetching templates
    Route::get('/api/list', [NotificationTemplateController::class, 'getTemplates'])->name('templates.api.list');
  });

  // Analytics Routes
  Route::prefix('analytics')->group(function () {
    Route::get('/dashboard', [NotificationAnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
    Route::get('/api/success-rate', [NotificationAnalyticsController::class, 'getSuccessRate'])->name('analytics.api.success-rate');
    Route::get('/api/cost-analytics', [NotificationAnalyticsController::class, 'getCostAnalytics'])->name('analytics.api.cost');
    Route::get('/api/usage-stats', [NotificationAnalyticsController::class, 'getUsageStats'])->name('analytics.api.usage');
    Route::get('/api/top-senders', [NotificationAnalyticsController::class, 'getTopSenders'])->name('analytics.api.top-senders');
  });

  // SMS Templates Routes
  Route::prefix('sms-templates')->group(function () {
    Route::get('/', [SmsTemplateController::class, 'index'])->name('sms-templates.index');
    Route::post('/', [SmsTemplateController::class, 'store'])->name('sms-templates.store');
    Route::put('/{smsTemplate}', [SmsTemplateController::class, 'update'])->name('sms-templates.update');
    Route::delete('/{smsTemplate}', [SmsTemplateController::class, 'destroy'])->name('sms-templates.destroy');
    Route::post('/{smsTemplate}/toggle', [SmsTemplateController::class, 'toggleActive'])->name('sms-templates.toggle');

    // API endpoints for use in bulk SMS form
    Route::get('/api/list', [SmsTemplateController::class, 'apiList'])->name('sms-templates.api.list');
    Route::get('/api/{smsTemplate}', [SmsTemplateController::class, 'apiShow'])->name('sms-templates.api.show');
  });

  Route::prefix('whatsapp-templates')->middleware('channel.enabled:whatsapp')->group(function () {
    Route::get('/', [WhatsappTemplateController::class, 'index'])->name('whatsapp-templates.index');
    Route::get('/api/list', [WhatsappTemplateController::class, 'apiList'])->name('whatsapp-templates.api.list');
    Route::post('/sync', [WhatsappTemplateController::class, 'sync'])->name('whatsapp-templates.sync');
  });
});

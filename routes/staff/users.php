<?php
use App\Http\Controllers\Staff\StaffDirectMessagingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\QualificationController;
use App\Http\Controllers\WorkHistoryController;
use Illuminate\Support\Facades\Route;

// Profile routes - outside access-hr so all authenticated staff can access
Route::prefix('staff')->middleware(['auth', 'throttle:auth', 'block.non.african'])->group(function () {
    Route::get('/profile', [UserController::class, 'openProfile'])->name('staff.profile');
    Route::post('/profile/update/{id}', [UserController::class, 'updateProfile'])->name('staff.profile-update');
    Route::put('/users/profile/details/{user}', [UserController::class, 'updateProfileDetails'])->name('users.update-profile-details');
    Route::post('/profile/avatar', [UserController::class, 'updateProfileAvatar'])->name('profile.update-avatar');
    Route::post('/profile/qualifications', [UserController::class, 'storeProfileQualification'])->name('profile.qualifications.store');
    Route::put('/profile/qualifications/{id}', [UserController::class, 'updateProfileQualification'])->name('profile.qualifications.update');
    Route::delete('/profile/qualifications/{id}', [UserController::class, 'destroyProfileQualification'])->name('profile.qualifications.destroy');
    Route::post('/profile/work-history', [UserController::class, 'storeProfileWorkHistory'])->name('profile.work-history.store');
    Route::put('/profile/work-history/{id}', [UserController::class, 'updateProfileWorkHistory'])->name('profile.work-history.update');
    Route::delete('/profile/work-history/{id}', [UserController::class, 'destroyProfileWorkHistory'])->name('profile.work-history.destroy');

    Route::prefix('messages')->name('staff.messages.')->middleware('staff.messages.enabled')->group(function () {
        Route::get('/', [StaffDirectMessagingController::class, 'inbox'])->name('inbox');
        Route::get('/recipients', [StaffDirectMessagingController::class, 'recipients'])->name('recipients');
        Route::get('/unread-count', [StaffDirectMessagingController::class, 'unreadCount'])->name('unread-count');
        Route::post('/conversations', [StaffDirectMessagingController::class, 'startConversation'])->name('start');
        Route::post('/heartbeat', [StaffDirectMessagingController::class, 'heartbeat'])->name('heartbeat');

        Route::middleware('staff.presence.enabled')->group(function () {
            Route::get('/launcher', [StaffDirectMessagingController::class, 'launcher'])->name('launcher');
        });

        Route::get('/{conversation}/updates', [StaffDirectMessagingController::class, 'updates'])->name('updates');
        Route::get('/{conversation}', [StaffDirectMessagingController::class, 'conversation'])->name('conversation');
        Route::post('/{conversation}/reply', [StaffDirectMessagingController::class, 'reply'])->name('reply');
        Route::post('/{conversation}/archive', [StaffDirectMessagingController::class, 'archive'])->name('archive');
        Route::post('/{conversation}/unarchive', [StaffDirectMessagingController::class, 'unarchive'])->name('unarchive');
    });
});

// Complete Profile routes - outside access-hr, inside auth
Route::prefix('profile')->middleware(['auth', 'throttle:auth', 'block.non.african'])->group(function () {
    Route::get('/complete', [UserController::class, 'showCompleteProfile'])->name('profile.complete');
    Route::post('/complete', [UserController::class, 'saveCompleteProfile'])->name('profile.complete.save');
    Route::post('/complete/qualification', [UserController::class, 'storeCompleteProfileQualification'])->name('profile.complete.add-qualification');
    Route::post('/complete/work-history', [UserController::class, 'storeCompleteProfileWorkHistory'])->name('profile.complete.add-work-history');
    Route::get('/complete/check', [UserController::class, 'checkProfileCompleteness'])->name('profile.complete.check');
});

Route::prefix('staff')->middleware(['auth', 'throttle:auth', 'block.non.african','can:access-hr'])->group(function () {

  Route::get('/', [UserController::class, 'index'])->name('staff.index');
  Route::get('/nationality', [UserController::class, 'userNationalityReport'])->name('staff.staff-usersByNationality');

  Route::get('/settings', [UserController::class, 'staffSettings'])->name('staff.staff-settings');
  Route::post('/filters/store', [UserController::class, 'storeFilter'])->name('filters.store-filter');
  Route::put('/update/filters/{id}', [UserController::class, 'updateFilter'])->name('filters.update-filter');
  Route::delete('delete/filters/{id}', [UserController::class, 'destroyFilter'])->name('filters.destroy-filter');
  Route::post('/settings/force-profile-update', [UserController::class, 'updateForceProfileSetting'])->name('staff.settings.force-profile-update');
  Route::post('/earning-bands/store', [UserController::class, 'storeEarningBand'])->name('staff.earning-bands.store');
  Route::post('/earning-bands/update/{id}', [UserController::class, 'updateEarningBand'])->name('staff.earning-bands.update');
  Route::delete('/earning-bands/delete/{id}', [UserController::class, 'destroyEarningBand'])->name('staff.earning-bands.destroy');

  Route::post('/department/update/{id}', [UserController::class, 'updateDepartment'])->name('staff.update-department');
  Route::get('/department/edit/{departmentId}', [UserController::class, 'editDepartment'])->name('staff.edit-department');
  Route::delete('/department/delete/{departmentId}', [UserController::class, 'deleteDepartment'])->name('staff.delete-department');
  Route::post('/department/add', [UserController::class, 'addDepartment'])->name('staff.add-department');
  Route::get('/department/show', [UserController::class, 'showDepartment'])->name('staff.show-department');
  
  // Qualification Management Routes
  Route::get('/qualification/show', [UserController::class, 'showQualification'])->name('staff.show-qualification');
  Route::post('/qualification/add', [UserController::class, 'addQualification'])->name('staff.add-qualification');
  Route::get('/qualification/edit/{qualificationId}', [UserController::class, 'editQualification'])->name('staff.edit-qualification');
  Route::post('/qualification/update/{id}', [UserController::class, 'updateQualification'])->name('staff.update-qualification');
  Route::delete('/qualification/delete/{qualificationId}', [UserController::class, 'deleteQualification'])->name('staff.delete-qualification');


  Route::get('/qualifications/{id}', [QualificationController::class, 'index'])->name('staff.add-x-qualifications');
  Route::get('/edit/qualification/{id}/{qualificationId}', [QualificationController::class, 'editQualification'])->name('staff.edit-x-qualification');
  Route::post('/store/qualification', [QualificationController::class, 'storeQualification'])->name('staff.store-x-qualification');
  Route::post('/update/qualification/{id}', [QualificationController::class, 'updateQualification'])->name('staff.update-x-qualification');
  Route::get('/remove/qualification/{userId}/{id}', [QualificationController::class, 'removeQualification'])->name('staff.remove-x-qualification');
  
  Route::post('/send-sms/{recipientType}/{id}', [UserController::class, 'sendDirectSms'])->middleware('channel.enabled:sms')->name('send.sms');
  Route::post('/send-message/{recipientType}/{id}', [UserController::class, 'sendDirectMessage'])->name('staff.send-message');
  Route::post('/{user}/communication-consent', [UserController::class, 'updateCommunicationConsent'])->name('staff.communication-consent');

  Route::get('/analysis/roles', [UserController::class, 'getUsersGroupedByRoles'])->name('staff.analysis-by-role');
  Route::get('/analysis/report', [UserController::class, 'analysisReport'])->name('staff.analysis-report');
  Route::get('/analysis/staff-by-filters', [UserController::class, 'staffByFilters'])->name('staff.staff-by-filters');
  Route::get('/analysis/area_of_work', [UserController::class, 'getUsersGroupedByAreaOfWork'])->name('staff.analysis-area-of-work');
  Route::get('/analsysis/departments', [UserController::class, 'usersByDepartment'])->name('staff.analysis-department');

  Route::post('/roles/allocations/{id}', [UserController::class, 'allocateRole'])->name('staff.staff-role-allocation');
  // Profile routes moved outside access-hr group so all staff can access their own profile

  Route::get('/new', [UserController::class, 'create'])->name('staff.staff-new');

  Route::get('/roles/allocations', [UserController::class, 'roleAllocations'])->name('staff.roles.allocations');
  Route::post('/allocate/class/teachers/roles', [UserController::class, 'allocateClassTeachersRoles'])->name('staff.allocate-class-teachers-roles');
  Route::post('/allocate/teachers/roles', [UserController::class, 'allocateTeachersRoles'])->name('staff.allocate-teachers-roles');
  Route::post('/allocate/bulk/roles', [UserController::class, 'processBulkRoleAllocation'])->name('staff.allocate-bulk-roles');

  Route::post('/create', [UserController::class, 'store'])->name('staff.staff-create');
  Route::get('/view/{id}', [UserController::class, 'show'])->name('staff.staff-view');
  Route::get('/academic-data/{id}', [UserController::class, 'academicData'])->name('staff.academic-data');
  Route::post('/update/{id}', [UserController::class, 'update'])->name('staff.staff-update');

  Route::post('/signature/{id}', [UserController::class, 'uploadSignature'])->name('staff.signature');
  Route::post('/sms/signature/{id}', [UserController::class, 'smsSignature'])->name('staff.sms-signature');

  Route::get('/generate-staff-report', [UserController::class, 'getUsersByAreaOfWork'])->name('staff.staff-pdf-by-area-of-work');
  Route::get('reset/{id}', [UserController::class, 'resetUserPassword'])->name('staff.reset');

  Route::get('/qualifications', [UserController::class, 'qualificationsReport'])->name('staff.qualifications');
  Route::get('/user-qualifications', [UserController::class, 'userQualificationsReport'])->name('staff.user-qualifications');
  Route::get('/organisation', [UserController::class, 'organogram'])->name('staff.organisational-reporting');

  Route::get('/analysis/export-staff', [UserController::class, 'analysisReportExport'])->name('staff.export-list-analysis');
  Route::get('/analysis/export-department', [UserController::class, 'usersByDepartmentExport'])->name('staff.export-analysis-department');
  Route::get('/analysis/export-qualifications', [UserController::class, 'userQualificationsExport'])->name('staff.export-analysis-qualifications');

  Route::get('/custom/analysis', [UserController::class, 'staffCustomAnalysis'])->name('staff.staff-custom-analysis');
  Route::post('/custom/get-fields', [UserController::class, 'getUserFields'])->name('staff.staff-get-fields');
  Route::post('/custom/generate-report', [UserController::class, 'generateUserReport'])->name('staff.staff-generate-report');

  Route::post('/add/work', [WorkHistoryController::class, 'storeWorkHistory'])->name('staff.store-work-history');
  Route::post('/update/work/{id}', [WorkHistoryController::class, 'updateWorkHistory'])->name('staff.update-work-history');
  Route::get('/add/work/{id}', [WorkHistoryController::class, 'addWorkHistory'])->name('staff.add-work-history');
  Route::get('/edit/work/{id}/{work}', [WorkHistoryController::class, 'editWorkHistory'])->name('staff.edit-work-history');
  Route::get('/delete/work/{id}', [WorkHistoryController::class, 'removeWorkHistory'])->name('staff.remove-work-history');
});

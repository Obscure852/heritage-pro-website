<?php

use App\Http\Controllers\Documents\CategoryController;
use App\Http\Controllers\Documents\DashboardController;
use App\Http\Controllers\Documents\DocumentController;
use App\Http\Controllers\Documents\FavoriteController;
use App\Http\Controllers\Documents\FolderController;
use App\Http\Controllers\Documents\NotificationController;
use App\Http\Controllers\Documents\SearchController;
use App\Http\Controllers\Documents\DocumentVersionController;
use App\Http\Controllers\Documents\PublicLinkController;
use App\Http\Controllers\Documents\ShareController;
use App\Http\Controllers\Documents\TagController;
use App\Http\Controllers\Documents\WorkflowController;
use App\Http\Controllers\Documents\AuditController;
use App\Http\Controllers\Documents\QuotaController;
use App\Http\Controllers\Documents\RetentionController;
use App\Http\Controllers\Documents\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Document Management Routes
|--------------------------------------------------------------------------
|
| Routes for the Document Management System including document CRUD,
| folder management, sharing, versioning, and administration.
|
| All routes require authentication via 'auth' middleware.
|
*/

Route::middleware(['auth'])->prefix('documents')->name('documents.')->group(function () {
    // ==================== Phase 2: Document CRUD ====================

    // Listing
    Route::get('/', [DocumentController::class, 'index'])->name('index');

    // ==================== Phase 10: Dashboard & Admin Settings ====================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/{section}', [SettingsController::class, 'updateSection'])->name('settings.update');

    // Create / Upload
    Route::get('/create', [DocumentController::class, 'create'])->name('create');
    Route::post('/', [DocumentController::class, 'store'])->name('store');

    // ==================== Phase 4: Search ====================
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

    // Trash listing (MUST be before {document} wildcard)
    Route::get('/trash/list', [DocumentController::class, 'trash'])->name('trash');

    // Bulk actions (MUST be before {document} wildcard)
    Route::post('/bulk/delete', [DocumentController::class, 'bulkDelete'])->name('bulk.delete');
    Route::post('/bulk/download', [DocumentController::class, 'bulkDownload'])->name('bulk.download');

    // ==================== Phase 3: Folder management routes ====================

    // Static folder routes MUST come before {folder} wildcard
    Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');
    Route::post('/folders/bulk-delete', [FolderController::class, 'bulkDestroy'])->name('folders.bulk.destroy');
    Route::post('/folders/move', [FolderController::class, 'moveItems'])->name('folders.move');
    Route::get('/folders/tree', [FolderController::class, 'tree'])->name('folders.tree');
    Route::patch('/folders/{folder}/access', [FolderController::class, 'updateAccess'])->name('folders.access');

    // Folder permission management (Phase 6)
    Route::get('/folders/{folder}/permissions', [FolderController::class, 'getFolderPermissions'])->name('folders.permissions.index');
    Route::post('/folders/{folder}/permissions', [FolderController::class, 'setFolderPermission'])->name('folders.permissions.store');
    Route::delete('/folders/{folder}/permissions', [FolderController::class, 'removeFolderPermission'])->name('folders.permissions.destroy');

    Route::put('/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

    // ==================== Phase 4: Categories, Tags & Search ====================

    // Category admin (index redirects to admin-settings tab)
    Route::get('/categories', fn () => redirect()->route('documents.settings', ['tab' => 'categories']));
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    // Tag admin (index redirects to admin-settings tab)
    Route::get('/tags', fn () => redirect()->route('documents.settings', ['tab' => 'tags']))->name('tags.index.redirect');
    Route::prefix('tags')->name('tags.')->group(function () {
        Route::post('/', [TagController::class, 'store'])->name('store');
        Route::put('/{tag}', [TagController::class, 'update'])->name('update');
        Route::delete('/{tag}', [TagController::class, 'destroy'])->name('destroy');
        Route::post('/merge', [TagController::class, 'merge'])->name('merge');
    });

    // Tag search API for Select2 (used by document create/edit forms)
    Route::get('/tags/search', [TagController::class, 'search'])->name('tags.search');

    // Favorites
    Route::post('/{document}/favorite', [FavoriteController::class, 'toggle'])->name('favorite.toggle');

    // ==================== Phase 6: Sharing routes ====================

    // Static sharing routes (MUST be before {document} wildcard)
    Route::get('/shares/users/search', [ShareController::class, 'userSearch'])->name('shares.users.search');
    Route::get('/shares/roles', [ShareController::class, 'roles'])->name('shares.roles');
    Route::get('/shares/departments', [ShareController::class, 'departments'])->name('shares.departments');
    Route::get('/shared', [ShareController::class, 'sharedWithMe'])->name('shared');

    // ==================== Phase 8: Workflow & Approval routes ====================
    Route::get('/workflow/reviewers/search', [WorkflowController::class, 'searchReviewers'])->name('workflow.reviewers.search');
    Route::post('/{document}/workflow/submit', [WorkflowController::class, 'submitForReview'])->name('workflow.submit');
    Route::post('/{document}/workflow/withdraw', [WorkflowController::class, 'withdraw'])->name('workflow.withdraw');
    Route::post('/{document}/workflow/review', [WorkflowController::class, 'review'])->name('workflow.review');
    Route::post('/{document}/workflow/publish', [WorkflowController::class, 'publish'])->name('workflow.publish');
    Route::post('/{document}/workflow/unpublish', [WorkflowController::class, 'unpublish'])->name('workflow.unpublish');
    Route::post('/{document}/workflow/archive', [WorkflowController::class, 'archive'])->name('workflow.archive');
    Route::post('/{document}/workflow/unarchive', [WorkflowController::class, 'unarchive'])->name('workflow.unarchive');

    // ==================== Phase 8: Notification routes ====================
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // ==================== Phase 5: Version control routes ====================
    Route::get('/{document}/versions/create', [DocumentVersionController::class, 'create'])->name('versions.create');
    Route::post('/{document}/versions', [DocumentVersionController::class, 'store'])->name('versions.store');
    Route::get('/{document}/versions/{version}/download', [DocumentVersionController::class, 'download'])->name('versions.download');
    Route::post('/{document}/versions/{version}/restore', [DocumentVersionController::class, 'restore'])->name('versions.restore');

    // ==================== Phase 7: Public link management (authenticated) ====================
    Route::get('/{document}/public-links', [PublicLinkController::class, 'index'])->name('public-links.index');
    Route::post('/{document}/public-links', [PublicLinkController::class, 'store'])->name('public-links.store');
    Route::patch('/{document}/public-links/{share}/disable', [PublicLinkController::class, 'disable'])->name('public-links.disable');
    Route::delete('/{document}/public-links/{share}', [PublicLinkController::class, 'destroy'])->name('public-links.destroy');

    // Publish/Feature toggle
    Route::post('/{document}/publish', [DocumentController::class, 'publish'])->name('publish');
    Route::post('/{document}/featured', [DocumentController::class, 'toggleFeatured'])->name('featured');

    // Per-document share routes
    Route::get('/{document}/shares', [ShareController::class, 'index'])->name('shares.index');
    Route::post('/{document}/shares', [ShareController::class, 'store'])->name('shares.store');
    Route::delete('/{document}/shares/{share}', [ShareController::class, 'destroy'])->name('shares.destroy');

    // ==================== Phase 9: Audit, Retention & Quotas ====================
    // Index routes redirect to admin-settings tabs; action routes stay unchanged
    Route::get('/audit-logs', fn () => redirect()->route('documents.settings', ['tab' => 'audit']));
    Route::get('/audit-logs/export', [AuditController::class, 'export'])->name('audit-logs.export');

    Route::get('/quotas', fn () => redirect()->route('documents.settings', ['tab' => 'quotas']));
    Route::prefix('quotas')->name('quotas.')->group(function () {
        Route::put('/{user}', [QuotaController::class, 'update'])->name('update');
        Route::post('/bulk', [QuotaController::class, 'bulkUpdate'])->name('bulk');
        Route::post('/{user}/recalculate', [QuotaController::class, 'recalculate'])->name('recalculate');
    });

    // Retention policies (index redirects; create/edit/action routes stay)
    Route::get('/retention-policies', fn () => redirect()->route('documents.settings', ['tab' => 'retention']));
    Route::prefix('retention-policies')->name('retention-policies.')->group(function () {
        Route::get('/create', [RetentionController::class, 'create'])->name('create');
        Route::post('/', [RetentionController::class, 'store'])->name('store');
        Route::get('/{policy}/edit', [RetentionController::class, 'edit'])->name('edit');
        Route::put('/{policy}', [RetentionController::class, 'update'])->name('update');
        Route::delete('/{policy}', [RetentionController::class, 'destroy'])->name('destroy');
    });
    Route::get('/expiring-soon', [RetentionController::class, 'expiringSoon'])->name('expiring-soon');
    Route::post('/{document}/renew-expiry', [RetentionController::class, 'renewExpiry'])->name('renew-expiry');

    // Legal hold toggle
    Route::post('/{document}/legal-hold', [DocumentController::class, 'toggleLegalHold'])->name('legal-hold');

    // Restore (uses raw {id} since trashed documents won't resolve via route model binding)
    Route::post('/{id}/restore', [DocumentController::class, 'restore'])->name('restore')
        ->where('id', '[0-9]+');

    // Single document routes (using route model binding)
    Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
    Route::get('/{document}/edit', [DocumentController::class, 'edit'])->name('edit');
    Route::put('/{document}', [DocumentController::class, 'update'])->name('update');
    Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');

    // File operations
    Route::get('/{document}/preview', [DocumentController::class, 'preview'])->name('preview');
    Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download');

});

// ==================== Phase 7: Public access routes (no authentication) ====================
Route::middleware(['public.rate_limit'])->prefix('documents/public')->name('documents.public.')->group(function () {
    Route::get('/portal', [PublicLinkController::class, 'portal'])->name('portal');
    Route::get('/portal/search', [PublicLinkController::class, 'portalSearch'])->name('portal.search');
    Route::get('/link/{token}', [PublicLinkController::class, 'publicView'])->name('view');
    Route::get('/link/{token}/preview', [PublicLinkController::class, 'publicPreview'])->name('preview');
    Route::get('/link/{token}/download', [PublicLinkController::class, 'publicDownload'])->name('download');
    Route::get('/link/{token}/password', [PublicLinkController::class, 'passwordPage'])->name('password');
    Route::post('/link/{token}/password', [PublicLinkController::class, 'verifyPassword'])->name('password.verify');
});

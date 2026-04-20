<?php

use App\Http\Controllers\Schemes\AdminDashboardController;
use App\Http\Controllers\Schemes\LessonPlanController;
use App\Http\Controllers\Schemes\SchemeController;
use App\Http\Controllers\Schemes\SchemeEntryController;
use App\Http\Controllers\Schemes\SchemeWorkflowController;
use App\Http\Controllers\Schemes\StandardSchemeController;
use App\Http\Controllers\Schemes\StandardSchemeEntryController;
use App\Http\Controllers\Schemes\StandardSchemeWorkflowController;
use App\Http\Controllers\Schemes\SyllabusController;
use App\Http\Controllers\Schemes\SyllabusObjectiveController;
use App\Http\Controllers\Schemes\SyllabusTopicController;
use App\Http\Controllers\Schemes\TeacherDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:access-schemes'])->group(function () {

    // ==================== STANDARD SCHEMES ====================
    // Static routes MUST be before {standardScheme} wildcard

    Route::get('/standard-schemes', [StandardSchemeController::class, 'index'])
        ->name('standard-schemes.index');

    Route::middleware('can:manage-standard-schemes')->group(function () {
        Route::get('/standard-schemes/grades', [StandardSchemeController::class, 'gradesForTerm'])
            ->name('standard-schemes.grades-for-term');
        Route::get('/standard-schemes/subjects', [StandardSchemeController::class, 'subjectsForContext'])
            ->name('standard-schemes.subjects-for-context');
        Route::get('/standard-schemes/create', [StandardSchemeController::class, 'create'])
            ->name('standard-schemes.create');
        Route::post('/standard-schemes', [StandardSchemeController::class, 'store'])
            ->name('standard-schemes.store');
    });

    Route::get('/standard-schemes/{standardScheme}/print', [StandardSchemeController::class, 'document'])
        ->name('standard-schemes.print');

    Route::get('/standard-schemes/{standardScheme}', [StandardSchemeController::class, 'show'])
        ->name('standard-schemes.show');

    Route::middleware('can:manage-standard-schemes')->group(function () {
        Route::post('/standard-schemes/{standardScheme}/clone', [StandardSchemeController::class, 'clone'])
            ->name('standard-schemes.clone');
        Route::delete('/standard-schemes/{standardScheme}', [StandardSchemeController::class, 'destroy'])
            ->name('standard-schemes.destroy');

        // Workflow
        Route::post('/standard-schemes/{standardScheme}/submit', [StandardSchemeWorkflowController::class, 'submit'])
            ->name('standard-schemes.submit');
        Route::post('/standard-schemes/{standardScheme}/place-under-review', [StandardSchemeWorkflowController::class, 'placeUnderReview'])
            ->name('standard-schemes.place-under-review');
        Route::post('/standard-schemes/{standardScheme}/approve', [StandardSchemeWorkflowController::class, 'approve'])
            ->name('standard-schemes.approve');
        Route::post('/standard-schemes/{standardScheme}/return-for-revision', [StandardSchemeWorkflowController::class, 'returnForRevision'])
            ->name('standard-schemes.return-for-revision');
        Route::post('/standard-schemes/{standardScheme}/publish', [StandardSchemeWorkflowController::class, 'publish'])
            ->name('standard-schemes.publish');
        Route::post('/standard-schemes/{standardScheme}/distribute', [StandardSchemeWorkflowController::class, 'distribute'])
            ->name('standard-schemes.distribute');
        Route::post('/standard-schemes/{standardScheme}/unpublish', [StandardSchemeWorkflowController::class, 'unpublish'])
            ->name('standard-schemes.unpublish');

        // Entry AJAX
        Route::put('/standard-schemes/{standardScheme}/entries/{entry}', [StandardSchemeEntryController::class, 'update'])
            ->name('standard-schemes.entries.update');
        Route::post('/standard-schemes/{standardScheme}/entries/{entry}/objectives', [StandardSchemeEntryController::class, 'syncObjectives'])
            ->name('standard-schemes.entries.objectives.sync');
    });

    // ==================== INDIVIDUAL SCHEMES ====================

    Route::get('/schemes', [SchemeController::class, 'index'])->name('schemes.index');

    // Scheme CRUD — static route first (must be before {scheme} wildcard)
    Route::get('/schemes/create', [SchemeController::class, 'create'])->name('schemes.create');
    Route::post('/schemes', [SchemeController::class, 'store'])->name('schemes.store');

    // Phase 4 — HOD dashboard (static GET, MUST be before {scheme} wildcard to avoid shadowing)
    Route::get('/schemes/hod/dashboard', [SchemeWorkflowController::class, 'hodDashboard'])
        ->name('schemes.hod.dashboard');

    // Phase 5 — Teacher dashboard (static GET, MUST be before {scheme} wildcard)
    Route::get('/schemes/teacher/dashboard', [TeacherDashboardController::class, 'index'])
        ->name('schemes.teacher.dashboard');

    // Supervisor dashboard (static GET, MUST be before {scheme} wildcard)
    Route::get('/schemes/supervisor/dashboard', [SchemeWorkflowController::class, 'supervisorDashboard'])
        ->name('schemes.supervisor.dashboard');

    // Phase 6 — Admin dashboard (static GET, MUST be before {scheme} wildcard)
    Route::get('/schemes/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('schemes.admin.dashboard');

    Route::get('/schemes/{scheme}/document', [SchemeController::class, 'document'])->name('schemes.document');
    Route::post('/schemes/{scheme}/email', [SchemeController::class, 'sendDocumentEmail'])->name('schemes.email-document');
    Route::get('/schemes/{scheme}', [SchemeController::class, 'show'])->name('schemes.show');
    Route::delete('/schemes/{scheme}', [SchemeController::class, 'destroy'])->name('schemes.destroy');

    // Clone
    Route::post('/schemes/{scheme}/clone', [SchemeController::class, 'clone'])->name('schemes.clone');

    // Phase 4 — Workflow transitions
    Route::post('/schemes/{scheme}/submit', [SchemeWorkflowController::class, 'submit'])
        ->name('schemes.submit');
    Route::post('/schemes/{scheme}/place-under-review', [SchemeWorkflowController::class, 'placeUnderReview'])
        ->name('schemes.place-under-review');
    Route::post('/schemes/{scheme}/approve', [SchemeWorkflowController::class, 'approve'])
        ->name('schemes.approve');
    Route::post('/schemes/{scheme}/return-for-revision', [SchemeWorkflowController::class, 'returnForRevision'])
        ->name('schemes.return-for-revision');
    Route::post('/schemes/{scheme}/supervisor-approve', [SchemeWorkflowController::class, 'supervisorApprove'])
        ->name('schemes.supervisor-approve');
    Route::post('/schemes/{scheme}/supervisor-return', [SchemeWorkflowController::class, 'supervisorReturnForRevision'])
        ->name('schemes.supervisor-return');
    Route::post('/schemes/{scheme}/publish-reference', [SchemeWorkflowController::class, 'publishReference'])
        ->name('schemes.publish-reference');
    Route::post('/schemes/{scheme}/unpublish-reference', [SchemeWorkflowController::class, 'unpublishReference'])
        ->name('schemes.unpublish-reference');

    // AJAX entry routes
    Route::put('/schemes/{scheme}/entries/{entry}', [SchemeEntryController::class, 'update'])
        ->name('schemes.entries.update');
    Route::post('/schemes/{scheme}/entries/{entry}/objectives', [SchemeEntryController::class, 'syncObjectives'])
        ->name('schemes.entries.objectives.sync');

    // Objective browser — accessible to all teachers with access-schemes gate
    Route::get('/syllabi/objectives/browse', [SyllabusController::class, 'objectiveBrowser'])
        ->name('syllabi.objectives.browse');
    Route::get('/syllabi/{syllabus}/document/preview', [SyllabusController::class, 'previewDocument'])
        ->name('syllabi.document.preview');

    // Phase 5 — Lesson plan CRUD
    Route::prefix('lesson-plans')->name('lesson-plans.')->group(function () {
        Route::get('/create', [LessonPlanController::class, 'create'])->name('create');
        Route::post('/', [LessonPlanController::class, 'store'])->name('store');
        Route::get('/{lessonPlan}', [LessonPlanController::class, 'show'])->name('show');
        Route::get('/{lessonPlan}/edit', [LessonPlanController::class, 'edit'])->name('edit');
        Route::put('/{lessonPlan}', [LessonPlanController::class, 'update'])->name('update');
        Route::delete('/{lessonPlan}', [LessonPlanController::class, 'destroy'])->name('destroy');
        Route::post('/{lessonPlan}/mark-taught', [LessonPlanController::class, 'markTaught'])->name('mark-taught');

        // Lesson plan review workflow
        Route::post('/{lessonPlan}/submit', [LessonPlanController::class, 'submit'])->name('submit');
        Route::post('/{lessonPlan}/supervisor-approve', [LessonPlanController::class, 'supervisorApprove'])->name('supervisor-approve');
        Route::post('/{lessonPlan}/supervisor-return', [LessonPlanController::class, 'supervisorReturn'])->name('supervisor-return');
        Route::post('/{lessonPlan}/approve', [LessonPlanController::class, 'approve'])->name('approve');
        Route::post('/{lessonPlan}/return-for-revision', [LessonPlanController::class, 'returnForRevision'])->name('return-for-revision');
    });
});

Route::middleware(['auth', 'can:manage-syllabi'])->group(function () {
    // Static routes FIRST — must be defined before the resource route to avoid
    // being swallowed by the {syllabus} wildcard segment
    Route::get('/syllabi/documents/search', [SyllabusController::class, 'documentSearch'])
        ->name('syllabi.documents.search');

    Route::get('/syllabi', [SyllabusController::class, 'index'])->name('syllabi.index');
    Route::get('/syllabi/create', [SyllabusController::class, 'create'])->name('syllabi.create');
    Route::post('/syllabi', [SyllabusController::class, 'store'])->name('syllabi.store');
    Route::get('/syllabi/{syllabus}/edit', [SyllabusController::class, 'edit'])->name('syllabi.edit');
});

Route::middleware(['auth', 'can:edit-syllabi'])->group(function () {
    Route::put('/syllabi/{syllabus}', [SyllabusController::class, 'update'])->name('syllabi.update');
    Route::delete('/syllabi/{syllabus}', [SyllabusController::class, 'destroy'])->name('syllabi.destroy');
    Route::post('/syllabi/{syllabus}/refresh-cache', [SyllabusController::class, 'refreshCache'])
        ->name('syllabi.refresh-cache');
    Route::post('/syllabi/{syllabus}/populate-from-cache', [SyllabusController::class, 'populateFromCache'])
        ->name('syllabi.populate-from-cache');
    Route::post('/syllabi/{syllabus}/preview-sync-from-cache', [SyllabusController::class, 'previewSyncFromCache'])
        ->name('syllabi.preview-sync-from-cache');
    Route::post('/syllabi/{syllabus}/sync-from-cache', [SyllabusController::class, 'syncFromCache'])
        ->name('syllabi.sync-from-cache');

    // Topic AJAX CRUD — nested under syllabus
    Route::post('/syllabi/{syllabus}/topics', [SyllabusTopicController::class, 'store'])
        ->name('syllabi.topics.store');
    Route::put('/syllabi/{syllabus}/topics/{topic}', [SyllabusTopicController::class, 'update'])
        ->name('syllabi.topics.update');
    Route::delete('/syllabi/{syllabus}/topics/{topic}', [SyllabusTopicController::class, 'destroy'])
        ->name('syllabi.topics.destroy');

    // Objective AJAX CRUD — nested under syllabus + topic
    Route::post('/syllabi/{syllabus}/topics/{topic}/objectives', [SyllabusObjectiveController::class, 'store'])
        ->name('syllabi.topics.objectives.store');
    Route::put('/syllabi/{syllabus}/topics/{topic}/objectives/{objective}', [SyllabusObjectiveController::class, 'update'])
        ->name('syllabi.topics.objectives.update');
    Route::delete('/syllabi/{syllabus}/topics/{topic}/objectives/{objective}', [SyllabusObjectiveController::class, 'destroy'])
        ->name('syllabi.topics.objectives.destroy');
});

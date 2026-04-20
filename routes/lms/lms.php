<?php

use App\Http\Controllers\Lms\AnalyticsController;
use App\Http\Controllers\Lms\AssignmentController;
use App\Http\Controllers\Lms\CalendarController;
use App\Http\Controllers\Lms\CourseController;
use App\Http\Controllers\Lms\DiscussionController;
use App\Http\Controllers\Lms\ModuleController;
use App\Http\Controllers\Lms\ContentController;
use App\Http\Controllers\Lms\EnrollmentController;
use App\Http\Controllers\Lms\GamificationController;
use App\Http\Controllers\Lms\GradebookController;
use App\Http\Controllers\Lms\H5pController;
use App\Http\Controllers\Lms\LearningPathController;
use App\Http\Controllers\Lms\LibraryController;
use App\Http\Controllers\Lms\LmsSettingsController;
use App\Http\Controllers\Lms\LtiController;
use App\Http\Controllers\Lms\RubricController;
use App\Http\Controllers\Lms\NotificationController;
use App\Http\Controllers\Lms\QuizController;
use App\Http\Controllers\Lms\ScormController;
use App\Http\Controllers\Lms\TeacherMessagingController;
use App\Http\Controllers\Lms\VideoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LMS Routes
|--------------------------------------------------------------------------
|
| Routes for the Learning Management System module.
|
*/

Route::prefix('lms')->middleware(['auth', 'can:access-lms'])->name('lms.')->group(function () {

    // Course Management
    Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('courses/create', [CourseController::class, 'create'])->name('courses.create')->middleware('can:manage-lms-courses');
    Route::get('courses/subjects-by-grade', [CourseController::class, 'getSubjectsByGrade'])->name('courses.subjects-by-grade');
    Route::get('courses/partial', [CourseController::class, 'partial'])->name('courses.partial');
    Route::post('courses', [CourseController::class, 'store'])->name('courses.store')->middleware('can:manage-lms-courses');
    Route::get('courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::get('courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit')->middleware('can:manage-lms-courses');
    Route::put('courses/{course}', [CourseController::class, 'update'])->name('courses.update')->middleware('can:manage-lms-courses');
    Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy')->middleware('can:manage-lms-courses');

    // Course Actions
    Route::post('courses/{course}/publish', [CourseController::class, 'publish'])->name('courses.publish')->middleware('can:manage-lms-courses');
    Route::post('courses/{course}/unpublish', [CourseController::class, 'unpublish'])->name('courses.unpublish')->middleware('can:manage-lms-courses');
    Route::post('courses/{course}/archive', [CourseController::class, 'archive'])->name('courses.archive')->middleware('can:manage-lms-courses');
    Route::post('courses/{course}/duplicate', [CourseController::class, 'duplicate'])->name('courses.duplicate')->middleware('can:manage-lms-courses');

    // LMS Settings Management
    Route::middleware('can:manage-lms-courses')->group(function () {
        Route::get('settings', [LmsSettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [LmsSettingsController::class, 'update'])->name('settings.update');
        Route::put('settings/single', [LmsSettingsController::class, 'updateSingle'])->name('settings.update.single');
        Route::post('settings/calendar', [LmsSettingsController::class, 'updateCalendarSettings'])->name('settings.calendar.update');
    });

    // Rubric Management
    Route::middleware('can:manage-lms-courses')->prefix('rubrics')->name('rubrics.')->group(function () {
        Route::get('/', [RubricController::class, 'index'])->name('index');
        Route::get('/create', [RubricController::class, 'create'])->name('create');
        Route::post('/', [RubricController::class, 'store'])->name('store');
        Route::get('/{rubric}/edit', [RubricController::class, 'edit'])->name('edit');
        Route::put('/{rubric}', [RubricController::class, 'update'])->name('update');
        Route::delete('/{rubric}', [RubricController::class, 'destroy'])->name('destroy');
        Route::post('/{rubric}/duplicate', [RubricController::class, 'duplicate'])->name('duplicate');

        // Criterion Management (nested under rubric)
        Route::get('/{rubric}/criteria/create', [RubricController::class, 'createCriterion'])->name('criteria.create');
        Route::post('/{rubric}/criteria', [RubricController::class, 'storeCriterion'])->name('criteria.store');
        Route::get('/{rubric}/criteria/{criterion}/edit', [RubricController::class, 'editCriterion'])->name('criteria.edit');
        Route::put('/{rubric}/criteria/{criterion}', [RubricController::class, 'updateCriterion'])->name('criteria.update');
        Route::delete('/{rubric}/criteria/{criterion}', [RubricController::class, 'destroyCriterion'])->name('criteria.destroy');
    });

    // Module Management (nested under courses)
    Route::get('courses/{course}/modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::get('courses/{course}/modules/create', [ModuleController::class, 'create'])->name('modules.create');
    Route::post('courses/{course}/modules', [ModuleController::class, 'store'])->name('modules.store');
    Route::get('modules/{module}/edit', [ModuleController::class, 'edit'])->name('modules.edit');
    Route::put('modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
    Route::delete('modules/{module}', [ModuleController::class, 'destroy'])->name('modules.destroy');
    Route::post('courses/{course}/modules/reorder', [ModuleController::class, 'reorder'])->name('modules.reorder');

    // Content Management (nested under modules)
    Route::get('modules/{module}/content', [ContentController::class, 'index'])->name('content.index');
    Route::get('modules/{module}/content/create', [ContentController::class, 'create'])->name('content.create');
    Route::post('modules/{module}/content', [ContentController::class, 'store'])->name('content.store');
    Route::get('modules/{module}/content/library-items', [ContentController::class, 'libraryItems'])->name('content.library-items');
    Route::get('content/{content}', [ContentController::class, 'show'])->name('content.show');
    Route::get('content/{content}/edit', [ContentController::class, 'edit'])->name('content.edit');
    Route::put('content/{content}', [ContentController::class, 'update'])->name('content.update');
    Route::delete('content/{content}', [ContentController::class, 'destroy'])->name('content.destroy');
    Route::post('modules/{module}/content/reorder', [ContentController::class, 'reorder'])->name('content.reorder');
    Route::get('library/items/{item}/preview', [ContentController::class, 'previewLibraryItem'])->name('content.library-preview');

    // Content Player (for students viewing content)
    Route::get('content/{content}/player', [ContentController::class, 'player'])->name('content.player');
    Route::post('content/{content}/progress', [ContentController::class, 'updateProgress'])->name('content.progress');
    Route::post('content/{content}/complete', [ContentController::class, 'markComplete'])->name('content.complete');

    // Enrollment Management
    Route::get('courses/{course}/enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::get('courses/{course}/enroll', [EnrollmentController::class, 'create'])->name('enrollments.create');
    Route::post('courses/{course}/enroll', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::post('courses/{course}/enroll/self', [EnrollmentController::class, 'selfEnroll'])->name('enrollments.self');
    Route::delete('enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy');
    Route::post('enrollments/{enrollment}/drop', [EnrollmentController::class, 'drop'])->name('enrollments.drop');

    // Student Course View - Moved to Student Portal (routes/student-auth/student.php)
    // Students should access these features through the Student Portal with auth:student middleware
    // Route::get('my-courses', [EnrollmentController::class, 'myCourses'])->name('my-courses');
    // Route::get('courses/{course}/learn', [EnrollmentController::class, 'learn'])->name('courses.learn');

    // Quiz Routes
    Route::get('quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::get('quizzes/{quiz}/edit', [QuizController::class, 'edit'])->name('quizzes.edit');
    Route::put('quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');
    Route::get('quizzes/{quiz}/questions', [QuizController::class, 'questions'])->name('quizzes.questions');
    Route::post('quizzes/{quiz}/questions', [QuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
    Route::put('questions/{question}', [QuizController::class, 'updateQuestion'])->name('quizzes.questions.update');
    Route::delete('questions/{question}', [QuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');
    Route::post('quizzes/{quiz}/questions/reorder', [QuizController::class, 'reorderQuestions'])->name('quizzes.questions.reorder');

    // Quiz Taking (Student)
    Route::post('quizzes/{quiz}/start', [QuizController::class, 'start'])->name('quizzes.start')->middleware('throttle:5,1');
    Route::get('quizzes/{quiz}/attempt/{attempt}', [QuizController::class, 'attempt'])->name('quizzes.attempt');
    Route::post('quizzes/{quiz}/attempt/{attempt}/answer', [QuizController::class, 'saveAnswer'])->name('quizzes.answer');
    Route::post('quizzes/{quiz}/attempt/{attempt}/submit', [QuizController::class, 'submit'])->name('quizzes.submit')->middleware('throttle:5,1');
    Route::get('quizzes/{quiz}/attempt/{attempt}/results', [QuizController::class, 'results'])->name('quizzes.results');

    // Quiz Grading (Teacher)
    Route::get('quizzes/{quiz}/attempts', [QuizController::class, 'attempts'])->name('quizzes.attempts');
    Route::get('quizzes/{quiz}/enrollments', [QuizController::class, 'enrollments'])->name('quizzes.enrollments');
    Route::get('quizzes/{quiz}/attempt/{attempt}/grade', [QuizController::class, 'gradeForm'])->name('quizzes.grade');
    Route::post('quizzes/{quiz}/attempt/{attempt}/grade', [QuizController::class, 'saveGrade'])->name('quizzes.grade.save');

    // Video Upload and Management
    Route::get('modules/{module}/videos/create', [VideoController::class, 'create'])->name('videos.create');
    Route::post('modules/{module}/videos', [VideoController::class, 'store'])->name('videos.store');
    Route::get('videos/{video}', [VideoController::class, 'show'])->name('videos.show');
    Route::get('videos/{video}/edit', [VideoController::class, 'edit'])->name('videos.edit');
    Route::put('videos/{video}', [VideoController::class, 'update'])->name('videos.update');
    Route::delete('videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');
    Route::get('videos/{video}/player', [VideoController::class, 'player'])->name('videos.player');

    // Video Transcoding
    Route::post('videos/{video}/transcode', [VideoController::class, 'transcode'])->name('videos.transcode');
    Route::get('videos/{video}/transcoding-status', [VideoController::class, 'transcodingStatus'])->name('videos.transcoding-status');

    // Video Progress API
    Route::post('videos/{video}/progress', [VideoController::class, 'updateProgress'])->name('videos.progress');
    Route::post('videos/{video}/event', [VideoController::class, 'logEvent'])->name('videos.event');

    // Assignment Routes
    Route::get('modules/{module}/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('modules/{module}/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::get('assignments/{assignment}/edit', [AssignmentController::class, 'edit'])->name('assignments.edit');
    Route::put('assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
    Route::post('assignments/{assignment}/publish', [AssignmentController::class, 'publish'])->name('assignments.publish');
    Route::post('assignments/{assignment}/close', [AssignmentController::class, 'close'])->name('assignments.close');
    Route::delete('assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');

    // Assignment Attachments (Instructor reference materials)
    Route::get('assignments/attachments/{attachment}/download', [AssignmentController::class, 'downloadAttachment'])
        ->name('assignments.attachment.download');

    // Assignment Submissions (Student)
    Route::get('assignments/{assignment}/submit', [AssignmentController::class, 'submitForm'])->name('assignments.submit.form');
    Route::post('assignments/{assignment}/submit', [AssignmentController::class, 'submit'])->name('assignments.submit')->middleware('throttle:5,1');

    // Assignment Grading (Teacher)
    Route::get('assignments/{assignment}/submissions', [AssignmentController::class, 'submissions'])->name('assignments.submissions');
    Route::get('assignments/{assignment}/enrollments', [AssignmentController::class, 'enrollments'])->name('assignments.enrollments');
    Route::get('submissions/{submission}/grade', [AssignmentController::class, 'gradeForm'])->name('submissions.grade');
    Route::post('submissions/{submission}/grade', [AssignmentController::class, 'saveGrade'])->name('submissions.grade.save');
    Route::get('submissions/files/{file}/download', [AssignmentController::class, 'downloadFile'])->name('submissions.download');

    // SCORM Routes
    Route::get('scorm', [ScormController::class, 'index'])->name('scorm.index')->middleware('can:manage-lms-content');
    Route::get('modules/{module}/scorm/create', [ScormController::class, 'create'])->name('scorm.create');
    Route::post('modules/{module}/scorm', [ScormController::class, 'store'])->name('scorm.store');
    Route::get('scorm/{package}', [ScormController::class, 'show'])->name('scorm.show')->middleware('can:manage-lms-content');
    Route::get('scorm/{package}/edit', [ScormController::class, 'edit'])->name('scorm.edit')->middleware('can:manage-lms-content');
    Route::put('scorm/{package}', [ScormController::class, 'update'])->name('scorm.update')->middleware('can:manage-lms-content');
    Route::get('scorm/{package}/preview', [ScormController::class, 'preview'])->name('scorm.preview')->middleware('can:manage-lms-content');
    Route::get('scorm/{package}/player', [ScormController::class, 'player'])->name('scorm.player');
    Route::get('scorm/{package}/player/{content}', [ScormController::class, 'player'])->name('scorm.player.content');
    Route::delete('scorm/{package}', [ScormController::class, 'destroy'])->name('scorm.destroy');

    // SCORM Runtime API
    Route::post('scorm/api/{attempt}/initialize', [ScormController::class, 'apiInitialize'])->name('scorm.api.initialize');
    Route::post('scorm/api/{attempt}/getValue', [ScormController::class, 'apiGetValue'])->name('scorm.api.getValue');
    Route::post('scorm/api/{attempt}/setValue', [ScormController::class, 'apiSetValue'])->name('scorm.api.setValue');
    Route::post('scorm/api/{attempt}/commit', [ScormController::class, 'apiCommit'])->name('scorm.api.commit');
    Route::post('scorm/api/{attempt}/terminate', [ScormController::class, 'apiTerminate'])->name('scorm.api.terminate');
    Route::post('scorm/api/{attempt}/batch', [ScormController::class, 'apiBatchUpdate'])->name('scorm.api.batch');

    // H5P Routes
    Route::get('modules/{module}/h5p/create', [H5pController::class, 'create'])->name('h5p.create');
    Route::post('modules/{module}/h5p', [H5pController::class, 'store'])->name('h5p.store');
    Route::get('h5p/{content}/player', [H5pController::class, 'player'])->name('h5p.player');
    Route::get('h5p/{content}/player/{item}', [H5pController::class, 'player'])->name('h5p.player.item');
    Route::delete('h5p/{content}', [H5pController::class, 'destroy'])->name('h5p.destroy');

    // H5P xAPI Events
    Route::post('h5p/{content}/xapi', [H5pController::class, 'xapiEvent'])->name('h5p.xapi');

    // ===== Gamification Routes =====

    // Student Gamification Views
    Route::get('gamification', [GamificationController::class, 'dashboard'])->name('gamification.dashboard');
    Route::get('gamification/badges', [GamificationController::class, 'badges'])->name('gamification.badges');
    Route::get('gamification/badges/{badge}', [GamificationController::class, 'showBadge'])->name('gamification.badges.show');
    Route::get('gamification/leaderboard', [GamificationController::class, 'leaderboard'])->name('gamification.leaderboard');
    Route::get('gamification/achievements', [GamificationController::class, 'achievements'])->name('gamification.achievements');
    Route::get('gamification/points-history', [GamificationController::class, 'pointsHistory'])->name('gamification.points-history');

    // Admin Gamification Management
    Route::middleware('can:manage-lms-content')->group(function () {
        Route::get('admin/gamification/badges', [GamificationController::class, 'manageBadges'])->name('gamification.admin.badges');
        Route::get('admin/gamification/badges/create', [GamificationController::class, 'createBadge'])->name('gamification.admin.badges.create');
        Route::post('admin/gamification/badges', [GamificationController::class, 'storeBadge'])->name('gamification.admin.badges.store');
        Route::post('admin/gamification/award-badge', [GamificationController::class, 'awardBadge'])->name('gamification.admin.award-badge');
        Route::post('admin/gamification/adjust-points', [GamificationController::class, 'adjustPoints'])->name('gamification.admin.adjust-points');
        Route::post('admin/gamification/refresh-leaderboards', [GamificationController::class, 'refreshLeaderboards'])->name('gamification.admin.refresh-leaderboards');
    });

    // ===== Discussion Forum Routes =====

    // Course Discussions
    Route::get('courses/{course}/discussions', [DiscussionController::class, 'forum'])->name('discussions.forum');
    Route::get('discussions/{forum}/threads/create', [DiscussionController::class, 'createThread'])->name('discussions.create-thread');
    Route::post('discussions/{forum}/threads', [DiscussionController::class, 'storeThread'])->name('discussions.store-thread');
    Route::get('discussions/{forum}/threads/{thread}', [DiscussionController::class, 'thread'])->name('discussions.thread');

    // Posts and Replies
    Route::post('discussions/threads/{thread}/posts', [DiscussionController::class, 'storePost'])->name('discussions.store-post');
    Route::put('discussions/posts/{post}', [DiscussionController::class, 'updatePost'])->name('discussions.update-post');
    Route::delete('discussions/posts/{post}', [DiscussionController::class, 'deletePost'])->name('discussions.delete-post');
    Route::post('discussions/posts/{post}/mark-answer', [DiscussionController::class, 'markAsAnswer'])->name('discussions.mark-answer');

    // Admin Discussion Management
    Route::middleware('can:manage-lms-content')->group(function () {
        Route::post('discussions/threads/{thread}/lock', [DiscussionController::class, 'toggleLock'])->name('discussions.toggle-lock');
        Route::post('discussions/threads/{thread}/pin', [DiscussionController::class, 'togglePin'])->name('discussions.toggle-pin');
    });

    // ===== Notification Routes =====

    // Student Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/dropdown', [NotificationController::class, 'dropdown'])->name('notifications.dropdown');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Notification Preferences
    Route::get('notifications/preferences', [NotificationController::class, 'preferences'])->name('notifications.preferences');
    Route::post('notifications/preferences', [NotificationController::class, 'savePreferences'])->name('notifications.preferences.save');

    // Announcements
    Route::get('announcements', [NotificationController::class, 'announcements'])->name('announcements');
    Route::get('announcements/{announcement}', [NotificationController::class, 'showAnnouncement'])->name('announcements.show');

    // Admin Announcements
    Route::middleware('can:manage-lms-content')->group(function () {
        Route::get('admin/announcements/create', [NotificationController::class, 'createAnnouncement'])->name('announcements.create');
        Route::post('admin/announcements', [NotificationController::class, 'storeAnnouncement'])->name('announcements.store');
        Route::post('announcements/{announcement}/publish', [NotificationController::class, 'publishAnnouncement'])->name('announcements.publish');
        Route::delete('announcements/{announcement}', [NotificationController::class, 'deleteAnnouncement'])->name('announcements.delete');
    });

    // ===== Learning Path Routes =====

    // Browse Learning Paths (Staff Management)
    Route::get('learning-paths', [LearningPathController::class, 'index'])->name('learning-paths.index');
    Route::get('learning-paths/{learningPath}', [LearningPathController::class, 'show'])->name('learning-paths.show');

    // Student Learning Path Features - Moved to Student Portal (routes/student-auth/student.php)
    // Students should access these features through the Student Portal with auth:student middleware
    // Route::get('learning-paths/my-paths', [LearningPathController::class, 'myPaths'])->name('learning-paths.my-paths');
    // Route::post('learning-paths/{learningPath}/enroll', [LearningPathController::class, 'enroll'])->name('learning-paths.enroll');
    // Route::get('learning-paths/{learningPath}/learn', [LearningPathController::class, 'learn'])->name('learning-paths.learn');
    // Route::post('learning-paths/{learningPath}/courses/{pathCourse}/start', [LearningPathController::class, 'startCourse'])->name('learning-paths.start-course');

    // Admin Learning Path Management
    Route::middleware('can:manage-lms-content')->group(function () {
        Route::get('admin/learning-paths/create', [LearningPathController::class, 'create'])->name('learning-paths.create');
        Route::post('admin/learning-paths', [LearningPathController::class, 'store'])->name('learning-paths.store');
        Route::get('admin/learning-paths/{learningPath}/edit', [LearningPathController::class, 'edit'])->name('learning-paths.edit');
        Route::put('admin/learning-paths/{learningPath}', [LearningPathController::class, 'update'])->name('learning-paths.update');
        Route::post('admin/learning-paths/{learningPath}/publish', [LearningPathController::class, 'togglePublish'])->name('learning-paths.toggle-publish');
        Route::delete('admin/learning-paths/{learningPath}', [LearningPathController::class, 'destroy'])->name('learning-paths.destroy');
    });

    // ===== Gradebook Routes =====

    // Student Gradebook
    Route::get('my-grades', [GradebookController::class, 'myGrades'])->name('gradebook.my-grades');
    Route::get('courses/{course}/grades', [GradebookController::class, 'studentView'])->name('gradebook.student');

    // Instructor Gradebook
    Route::middleware('can:manage-lms-content')->group(function () {
        Route::get('courses/{course}/gradebook', [GradebookController::class, 'index'])->name('gradebook.index');
        Route::get('courses/{course}/gradebook/settings', [GradebookController::class, 'settings'])->name('gradebook.settings');
        Route::put('courses/{course}/gradebook/settings', [GradebookController::class, 'updateSettings'])->name('gradebook.settings.update');
        Route::get('courses/{course}/gradebook/export', [GradebookController::class, 'export'])->name('gradebook.export');
        Route::post('courses/{course}/gradebook/recalculate', [GradebookController::class, 'recalculate'])->name('gradebook.recalculate');
        Route::post('courses/{course}/gradebook/finalize', [GradebookController::class, 'finalizeGrades'])->name('gradebook.finalize');

        // Grade Categories
        Route::post('courses/{course}/gradebook/categories', [GradebookController::class, 'storeCategory'])->name('gradebook.categories.store');
        Route::put('gradebook/categories/{category}', [GradebookController::class, 'updateCategory'])->name('gradebook.categories.update');
        Route::delete('gradebook/categories/{category}', [GradebookController::class, 'destroyCategory'])->name('gradebook.categories.destroy');

        // Grade Items
        Route::post('courses/{course}/gradebook/items', [GradebookController::class, 'storeItem'])->name('gradebook.items.store');
        Route::put('gradebook/items/{item}', [GradebookController::class, 'updateItem'])->name('gradebook.items.update');
        Route::delete('gradebook/items/{item}', [GradebookController::class, 'destroyItem'])->name('gradebook.items.destroy');

        // Grading
        Route::get('gradebook/items/{item}/grade', [GradebookController::class, 'gradeItem'])->name('gradebook.items.grade');
        Route::post('gradebook/items/{item}/grades', [GradebookController::class, 'saveGrades'])->name('gradebook.items.save-grades');
        Route::post('gradebook/quick-grade', [GradebookController::class, 'quickGrade'])->name('gradebook.quick-grade');
        Route::post('gradebook/grades/{grade}/override', [GradebookController::class, 'overrideGrade'])->name('gradebook.grades.override');
        Route::get('gradebook/grades/{grade}/history', [GradebookController::class, 'gradeHistory'])->name('gradebook.grades.history');
    });

    // ===== Analytics Routes =====

    // Student Analytics
    Route::get('my-analytics', [AnalyticsController::class, 'myAnalytics'])->name('analytics.my');

    // Course Analytics (Instructor)
    Route::middleware('can:manage-lms-content')->group(function () {
        Route::get('courses/{course}/analytics', [AnalyticsController::class, 'courseDashboard'])->name('analytics.course');
        Route::get('courses/{course}/analytics/students', [AnalyticsController::class, 'studentAnalytics'])->name('analytics.students');
        Route::get('courses/{course}/analytics/students/{student}', [AnalyticsController::class, 'studentDetail'])->name('analytics.student-detail');
        Route::get('courses/{course}/analytics/content', [AnalyticsController::class, 'contentAnalytics'])->name('analytics.content');
        Route::get('courses/{course}/analytics/quizzes', [AnalyticsController::class, 'quizAnalytics'])->name('analytics.quizzes');
        Route::get('courses/{course}/analytics/engagement', [AnalyticsController::class, 'engagementAnalytics'])->name('analytics.engagement');
        Route::post('courses/{course}/analytics/refresh', [AnalyticsController::class, 'refreshCourseAnalytics'])->name('analytics.refresh');
    });

    // Reports
    Route::get('reports', [AnalyticsController::class, 'reports'])->name('analytics.reports');
    Route::post('reports/definitions', [AnalyticsController::class, 'createReport'])->name('analytics.create-report');
    Route::post('reports/generate', [AnalyticsController::class, 'generateReport'])->name('analytics.generate-report');
    Route::get('reports/{report}/download', [AnalyticsController::class, 'downloadReport'])->name('analytics.download-report');
    Route::delete('reports/{report}', [AnalyticsController::class, 'deleteReport'])->name('analytics.delete-report');
    Route::post('reports/{report}/retry', [AnalyticsController::class, 'retryReport'])->name('analytics.retry-report');
    Route::post('insights/{insight}/dismiss', [AnalyticsController::class, 'dismissInsight'])->name('analytics.dismiss-insight');

    // ===== Calendar Routes =====

    // Student Calendar
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    Route::get('calendar/preferences', [CalendarController::class, 'preferences'])->name('calendar.preferences');
    Route::put('calendar/preferences', [CalendarController::class, 'updatePreferences'])->name('calendar.update-preferences');
    Route::get('calendar/deadlines', [CalendarController::class, 'upcomingDeadlines'])->name('calendar.deadlines');

    // Calendar Events
    Route::post('calendar', [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('calendar/{event}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('calendar/{event}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::post('calendar/{event}/reminders', [CalendarController::class, 'createReminder'])->name('calendar.create-reminder');

    // Course Calendar
    Route::get('courses/{course}/calendar', [CalendarController::class, 'courseCalendar'])->name('calendar.course');

    // Appointments (Student)
    Route::get('appointments', [CalendarController::class, 'myAppointments'])->name('calendar.my-appointments');
    Route::get('schedules/{schedule}/slots', [CalendarController::class, 'availableSlots'])->name('calendar.available-slots');
    Route::post('schedules/{schedule}/book', [CalendarController::class, 'bookAppointment'])->name('calendar.book-appointment');
    Route::post('appointments/{appointment}/cancel', [CalendarController::class, 'cancelAppointment'])->name('calendar.cancel-appointment');

    // Availability Management (Instructor)
    Route::middleware('can:manage-lms-content')->group(function () {
        Route::get('availability', [CalendarController::class, 'manageAvailability'])->name('calendar.availability');
        Route::post('availability', [CalendarController::class, 'storeAvailability'])->name('calendar.store-availability');
        Route::post('availability/{schedule}/overrides', [CalendarController::class, 'storeOverride'])->name('calendar.store-override');
    });

    // ===== Teacher Messaging Routes =====
    Route::get('messaging', [TeacherMessagingController::class, 'inbox'])->name('messaging.inbox');
    Route::get('messaging/unread-count', [TeacherMessagingController::class, 'unreadCount'])->name('messaging.unread-count');
    Route::get('messaging/{conversation}', [TeacherMessagingController::class, 'conversation'])->name('messaging.conversation');
    Route::post('messaging/{conversation}/reply', [TeacherMessagingController::class, 'reply'])->name('messaging.reply');
    Route::post('messaging/{conversation}/archive', [TeacherMessagingController::class, 'archive'])->name('messaging.archive');
    Route::post('messaging/{conversation}/unarchive', [TeacherMessagingController::class, 'unarchive'])->name('messaging.unarchive');

    // ===== Content Library Routes =====

    // Browse Library
    Route::get('library', [LibraryController::class, 'index'])->name('library.index');
    Route::get('library/favorites', [LibraryController::class, 'favorites'])->name('library.favorites');
    Route::get('library/templates', [LibraryController::class, 'templates'])->name('library.templates');

    // Collections
    Route::post('library/collections', [LibraryController::class, 'createCollection'])->name('library.collections.store');
    Route::get('library/collections/{collection}', [LibraryController::class, 'collection'])->name('library.collection');

    // Items
    Route::post('library/items', [LibraryController::class, 'upload'])->name('library.upload');
    Route::get('library/items/{item}', [LibraryController::class, 'show'])->name('library.item');
    Route::get('library/items/{item}/edit', [LibraryController::class, 'edit'])->name('library.edit');
    Route::put('library/items/{item}', [LibraryController::class, 'update'])->name('library.update');
    Route::delete('library/items/{item}', [LibraryController::class, 'destroy'])->name('library.destroy');

    // Item Actions
    Route::post('library/items/{item}/versions', [LibraryController::class, 'uploadVersion'])->name('library.upload-version');
    Route::post('library/items/{item}/favorite', [LibraryController::class, 'toggleFavorite'])->name('library.toggle-favorite');
    Route::post('library/items/{item}/use', [LibraryController::class, 'useInCourse'])->name('library.use-in-course');

    // Templates
    Route::post('library/templates', [LibraryController::class, 'createTemplate'])->name('library.create-template');

    // ===== LTI Integration Routes =====

    // Admin LTI Tool Management
    Route::middleware('can:manage-lms-content')->group(function () {
        Route::get('lti', [LtiController::class, 'index'])->name('lti.index');
        Route::get('lti/create', [LtiController::class, 'create'])->name('lti.create');
        Route::post('lti', [LtiController::class, 'store'])->name('lti.store');
        Route::get('lti/{tool}', [LtiController::class, 'show'])->name('lti.show');
        Route::get('lti/{tool}/edit', [LtiController::class, 'edit'])->name('lti.edit');
        Route::put('lti/{tool}', [LtiController::class, 'update'])->name('lti.update');
        Route::delete('lti/{tool}', [LtiController::class, 'destroy'])->name('lti.destroy');
        Route::post('lti/{tool}/placements', [LtiController::class, 'addPlacement'])->name('lti.add-placement');
    });

    // Course LTI Tool Management
    Route::get('courses/{course}/lti-tools', [LtiController::class, 'courseTools'])->name('lti.course-tools');
    Route::post('courses/{course}/lti-tools/{tool}/toggle', [LtiController::class, 'toggleCourseTool'])->name('lti.toggle-course-tool');

    // LTI Launch
    Route::get('lti/{tool}/launch', [LtiController::class, 'launch'])->name('lti.launch');

    // AGS (Assignment and Grade Services)
    Route::get('courses/{course}/lti/line-items', [LtiController::class, 'lineItems'])->name('lti.line-items');
    Route::post('lti/line-items/{lineItem}/scores', [LtiController::class, 'submitScore'])->name('lti.submit-score');
});

// LTI 1.3 JWKS Endpoint (public, no auth required)
Route::get('lms/lti/jwks', [LtiController::class, 'jwks'])->name('lms.lti.jwks');

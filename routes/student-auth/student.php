<?php

use App\Http\Controllers\StudentAuth\StudentForgotPasswordController;
use App\Http\Controllers\StudentAuth\StudentLoginController;
use App\Http\Controllers\StudentAuth\StudentResetPasswordController;
use App\Http\Controllers\Auth\IdleSessionController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\StudentPortal\StudentLmsController;
use App\Http\Controllers\StudentPortal\StudentMessagingController;
use App\Http\Controllers\StudentPortal\StudentDiscussionController;

Route::prefix('student')->middleware(['block.non.african'])->group(function () {
    Route::get('/show/login', [StudentLoginController::class, 'showLoginForm'])->name('student.login');
    Route::post('/create/login', [StudentLoginController::class, 'studentLogin'])->name('student.login.post');
    Route::get('/logout', [StudentLoginController::class, 'logout'])->name('student.logout');

    Route::get('/password/reset', [StudentForgotPasswordController::class, 'showLinkRequestForm'])->name('student.password.request');
    Route::post('password/email', [StudentForgotPasswordController::class, 'sendResetLinkEmail'])->name('student.password.email');

    Route::get('/password/reset/{token}', [StudentResetPasswordController::class, 'showResetForm'])->name('student.password.reset');
    Route::post('/password/reset', [StudentResetPasswordController::class, 'reset'])->name('student.password.update');
    Route::post('/term/session', [StudentPortalController::class, 'setTermSession'])->name('student.term-session');
    Route::post('/grade/session', [StudentPortalController::class, 'setGradeSession'])->name('student.grade-session');

    Route::middleware('auth:student')->group(function () {
        Route::post('/activity', [IdleSessionController::class, 'touchStudent'])->name('student.activity');
        Route::get('/dashboard', [StudentPortalController::class, 'index'])->name('student.dashboard');
        Route::get('/term/dashboard', [StudentPortalController::class, 'getDashboardTermData'])->name('student.dashboard-term');

        // Profile Routes
        Route::get('/profile', [StudentPortalController::class, 'profile'])->name('student.profile');
        Route::post('/profile/update', [StudentPortalController::class, 'updateProfile'])->name('student.profile.update');
        Route::post('/profile/password', [StudentPortalController::class, 'updatePassword'])->name('student.profile.password');

        // ===== Academic Performance Routes =====
        Route::get('/academic', [StudentPortalController::class, 'academicIndex'])
            ->name('student.academic.index');
        Route::get('/academic/performance', [StudentPortalController::class, 'getAcademicPerformance'])
            ->name('student.academic.performance');
        Route::get('/academic/report-cards', [StudentPortalController::class, 'getReportCards'])
            ->name('student.academic.report-cards');
        Route::get('/academic/report-card/pdf', [StudentPortalController::class, 'viewReportCardPdf'])
            ->name('student.academic.report-card-pdf');
        Route::get('/academic/books', [StudentPortalController::class, 'getBooks'])
            ->name('student.academic.books');

        // ===== LMS Routes for Student Portal =====
        Route::prefix('lms')->name('student.lms.')->group(function () {
            // My Courses
            Route::get('/my-courses', [StudentLmsController::class, 'myCourses'])->name('my-courses');
            Route::get('/courses/{course}/learn', [StudentLmsController::class, 'learn'])->name('learn');

            // SCORM Player
            Route::get('/courses/{course}/scorm/{contentItem}', [StudentLmsController::class, 'playScorm'])->name('scorm.play');

            // Content Progress
            Route::post('/courses/{course}/content/{contentItem}/complete', [StudentLmsController::class, 'markContentComplete'])->name('content.complete');

            // Browse Courses
            Route::get('/courses', [StudentLmsController::class, 'courses'])->name('courses');
            Route::get('/courses/{course}', [StudentLmsController::class, 'showCourse'])->name('course');
            Route::post('/courses/{course}/enroll', [StudentLmsController::class, 'selfEnroll'])->name('enroll');
            Route::post('/enrollments/{enrollment}/drop', [StudentLmsController::class, 'dropCourse'])->name('drop');

            // Learning Paths
            Route::get('/learning-paths', [StudentLmsController::class, 'learningPaths'])->name('learning-paths');
            Route::get('/learning-paths/my-paths', [StudentLmsController::class, 'myLearningPaths'])->name('my-learning-paths');
            Route::get('/learning-paths/{learningPath}', [StudentLmsController::class, 'showLearningPath'])->name('learning-path');
            Route::post('/learning-paths/{learningPath}/enroll', [StudentLmsController::class, 'enrollInPath'])->name('learning-path.enroll');
            Route::get('/learning-paths/{learningPath}/learn', [StudentLmsController::class, 'learnPath'])->name('learning-path.learn');

            // Calendar
            Route::get('/calendar', [StudentLmsController::class, 'calendar'])->name('calendar.index');
            Route::get('/calendar/events', [StudentLmsController::class, 'calendarEvents'])->name('calendar.events');
            Route::get('/calendar/deadlines', [StudentLmsController::class, 'upcomingDeadlines'])->name('calendar.deadlines');

            // Appointments
            Route::get('/appointments', [StudentLmsController::class, 'myAppointments'])->name('appointments.index');
            Route::get('/appointments/schedules/{schedule}/slots', [StudentLmsController::class, 'availableSlots'])->name('appointments.slots');
            Route::post('/appointments/schedules/{schedule}/book', [StudentLmsController::class, 'bookAppointment'])->name('appointments.book');
            Route::post('/appointments/{appointment}/cancel', [StudentLmsController::class, 'cancelAppointment'])->name('appointments.cancel');

            // Direct Messaging
            Route::get('/messages', [StudentMessagingController::class, 'inbox'])->name('messages.inbox');
            Route::get('/messages/compose', [StudentMessagingController::class, 'compose'])->name('messages.compose');
            Route::post('/messages', [StudentMessagingController::class, 'send'])->name('messages.send');
            Route::get('/messages/unread-count', [StudentMessagingController::class, 'unreadCount'])->name('messages.unread-count');
            Route::get('/messages/{conversation}', [StudentMessagingController::class, 'conversation'])->name('messages.conversation');
            Route::post('/messages/{conversation}/reply', [StudentMessagingController::class, 'reply'])->name('messages.reply');
            Route::post('/messages/{conversation}/archive', [StudentMessagingController::class, 'archive'])->name('messages.archive');
            Route::post('/messages/{conversation}/unarchive', [StudentMessagingController::class, 'unarchive'])->name('messages.unarchive');

            // Discussions
            Route::get('/courses/{course}/discussions', [StudentDiscussionController::class, 'forum'])->name('discussions.forum');
            Route::get('/courses/{course}/discussions/create', [StudentDiscussionController::class, 'createThread'])->name('discussions.create');
            Route::post('/courses/{course}/discussions', [StudentDiscussionController::class, 'storeThread'])->name('discussions.store');
            Route::get('/discussions/threads/{thread}', [StudentDiscussionController::class, 'thread'])->name('discussions.thread');
            Route::post('/discussions/threads/{thread}/posts', [StudentDiscussionController::class, 'storePost'])->name('discussions.post');
            Route::post('/discussions/posts/{post}/answer', [StudentDiscussionController::class, 'markAsAnswer'])->name('discussions.answer');

            // Content-Specific Discussions
            Route::get('/content/{contentItem}/discussions', [StudentDiscussionController::class, 'contentDiscussions'])->name('discussions.content');
            Route::post('/content/{contentItem}/discussions', [StudentDiscussionController::class, 'storeContentThread'])->name('discussions.content.store');
        });
    });
});

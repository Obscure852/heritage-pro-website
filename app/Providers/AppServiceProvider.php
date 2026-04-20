<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use App\Observers\GeneralObserver;
use App\Observers\UserObserver;
use App\Observers\Fee\StudentInvoiceObserver;
use App\Models\Fee\StudentInvoice;
use App\Models\User;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use App\Models\SchoolSetup;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider{
    public function register()
    {
        $this->app->singleton(\App\Services\SchoolModeResolver::class, function () {
            return new \App\Services\SchoolModeResolver();
        });

        $this->app->singleton(\App\Services\SchoolModeProvisioner::class, function ($app) {
            return new \App\Services\SchoolModeProvisioner(
                $app->make(\App\Services\SchoolModeResolver::class)
            );
        });

        $this->app->singleton(\App\Services\ModuleVisibilityService::class, function ($app) {
            return new \App\Services\ModuleVisibilityService(
                $app->make(\App\Services\SettingsService::class)
            );
        });

        $this->app->singleton(\App\Services\Messaging\CommunicationChannelService::class, function ($app) {
            return new \App\Services\Messaging\CommunicationChannelService(
                $app->make(\App\Services\SettingsService::class)
            );
        });

        $this->app->singleton(\App\Services\Messaging\StaffMessagingFeatureService::class, function ($app) {
            return new \App\Services\Messaging\StaffMessagingFeatureService(
                $app->make(\App\Services\SettingsService::class)
            );
        });
    }

    public function boot(){
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        // Register morph map for polymorphic relationships (library borrower, etc.)
        Relation::morphMap([
            'student' => \App\Models\Student::class,
            'user' => \App\Models\User::class,
            'sponsor' => \App\Models\Sponsor::class,
        ]);

        // Verify storage symlink points to correct location
        $this->verifyStorageSymlink();

        if ($this->app->runningInConsole()) {
            config(['license.skip_validation' => true]);
        }

        Event::listen(MigrationsEnded::class, function () {
            if (app()->environment() !== 'testing') {
                Artisan::call('cache:clear');
                Artisan::call('view:clear');
                Artisan::call('route:clear');
                Artisan::call('config:clear');
                
                Artisan::call('route:cache');
                Artisan::call('config:cache');
            }
        });

        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', 300);
        ini_set('upload_max_filesize', '128M');
        ini_set('post_max_size', '128M');

        // Models to log - excludes high-volume records (scores, tests, attendance, comments, behaviour)
        $models = [
            \App\Models\User::class,
            \App\Models\Sponsor::class,
            \App\Models\Admission::class,
            \App\Models\Student::class,
            \App\Models\House::class,
            \App\Models\Klass::class,
            \App\Models\KlassSubject::class,
            \App\Models\Subject::class,
            \App\Models\Qualification::class,
            \App\Models\StudentTerm::class,
            \App\Models\GradeSubject::class,
            \App\Models\Notification::class,
            \App\Models\OptionalSubject::class,
            \App\Models\SchoolSetup::class,
        ];

        foreach ($models as $model) {
            if (class_exists($model)) {
                $model::observe(GeneralObserver::class);
            }
        }

        // Register Fee module observers
        StudentInvoice::observe(StudentInvoiceObserver::class);

        // Register User observer for auto-creating personal document folders (FLD-09)
        User::observe(UserObserver::class);

        view()->composer('*', function ($view) {
            $schoolModeResolver = app(\App\Services\SchoolModeResolver::class);
            $schoolType = Cache::remember('school_type', 60*24, function () {
                return SchoolSetup::select('type')->first();
            });
            $requestedContext = request()->query('context');
            $gradebookContext = $requestedContext ?: session('assessment_gradebook_context');
            $markbookContext = $requestedContext ?: session('assessment_markbook_context');
            $view->with('schoolType', $schoolType);
            $view->with('schoolModeResolver', $schoolModeResolver);
            $view->with('resolvedSchoolMode', $schoolModeResolver->mode());
            $view->with('gradebookCurrentContext', $gradebookContext);
            $view->with('gradebookBackUrl', $schoolModeResolver->gradebookUrl($gradebookContext));
            $view->with('markbookCurrentContext', $markbookContext);
            $view->with('markbookBackUrl', $schoolModeResolver->markbookUrl($markbookContext));
            $view->with('communicationChannels', app(\App\Services\Messaging\CommunicationChannelService::class)->toArray());
            $view->with('staffMessagingFeatures', app(\App\Services\Messaging\StaffMessagingFeatureService::class)->toArray());
        });

        // Sponsor portal sidebar - pass sponsor's students for dynamic menu
        view()->composer('layouts.sidebar-sponsor', function ($view) {
            if (auth('sponsor')->check()) {
                $sponsorStudents = auth('sponsor')->user()->students()
                    ->select('id', 'first_name', 'last_name')
                    ->get();
                $view->with('sponsorStudents', $sponsorStudents);
            } else {
                $view->with('sponsorStudents', collect());
            }
        });
    }

    /**
     * Verify and fix storage symlink if pointing to wrong location.
     */
    protected function verifyStorageSymlink(): void
    {
        $publicStoragePath = public_path('storage');
        $expectedTarget = storage_path('app/public');

        // Check if symlink exists
        if (!file_exists($publicStoragePath)) {
            // Create the symlink if it doesn't exist
            @symlink($expectedTarget, $publicStoragePath);
            return;
        }

        // Check if it's a symlink and points to the correct location
        if (is_link($publicStoragePath)) {
            $currentTarget = readlink($publicStoragePath);

            // If pointing to wrong location, fix it
            if ($currentTarget !== $expectedTarget) {
                @unlink($publicStoragePath);
                @symlink($expectedTarget, $publicStoragePath);
            }
        }
    }
}

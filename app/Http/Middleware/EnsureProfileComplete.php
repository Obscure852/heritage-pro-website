<?php

namespace App\Http\Middleware;

use App\Models\StaffProfileSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class EnsureProfileComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if (!Schema::hasTable('staff_profile_settings')) {
            return $next($request);
        }

        $enabled = Cache::remember('force_profile_update_enabled', 60, function () {
            return StaffProfileSetting::isForceUpdateEnabled();
        });

        if (!$enabled) {
            return $next($request);
        }

        if ($user->hasRoles('Administrator')) {
            return $next($request);
        }

        $whitelistedRoutes = [
            'profile.complete',
            'profile.complete.save',
            'profile.complete.add-qualification',
            'profile.complete.add-work-history',
            'profile.complete.check',
            'profile.update-avatar',
            'staff.profile',
            'staff.profile-update',
            'users.update-profile-details',
            'profile.qualifications.store',
            'profile.qualifications.update',
            'profile.qualifications.destroy',
            'profile.work-history.store',
            'profile.work-history.update',
            'profile.work-history.destroy',
            'logout',
            'login',
        ];

        $routeName = $request->route()?->getName();
        if ($routeName && in_array($routeName, $whitelistedRoutes)) {
            return $next($request);
        }

        if ($routeName && str_starts_with($routeName, 'password.')) {
            return $next($request);
        }

        $incomplete = StaffProfileSetting::getIncompleteItems($user);
        $isIncomplete = !empty($incomplete['missing_fields']) || !empty($incomplete['missing_sections']);

        if ($isIncomplete) {
            session(['profile_completion_required' => true]);
            return redirect()->route('profile.complete');
        }

        // Even if data is complete, keep blocking until user explicitly
        // clicks "Save & Complete" which clears this session flag.
        if (session('profile_completion_required')) {
            return redirect()->route('profile.complete');
        }

        return $next($request);
    }
}

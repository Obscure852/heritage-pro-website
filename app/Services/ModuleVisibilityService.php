<?php

namespace App\Services;

use App\Models\User;
use App\Models\SMSApiSetting;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;

class ModuleVisibilityService
{
    protected const CACHE_KEY = 'module_visibility';
    protected const CACHE_TTL = 3600;

    protected SettingsService $settingsService;

    protected array $modules = [
        'welfare' => [
            'key' => 'modules.welfare_visible',
            'name' => 'Student Welfare',
            'icon' => 'fas fa-hospital-user',
            'roles' => ['School Counsellor', 'Welfare Admin', 'Welfare View', 'Nurse'],
        ],
        'communications' => [
            'key' => 'modules.communications_visible',
            'name' => 'Communication',
            'icon' => 'bx bxs-chat',
            'roles' => ['Communications Admin', 'Communications Edit', 'Communications View', 'SMS Admin', 'Bulk Report Cards'],
        ],
        'assets' => [
            'key' => 'modules.assets_visible',
            'name' => 'Assets',
            'icon' => 'bx bxs-package',
            'roles' => ['Asset Management Admin', 'Asset Management Edit', 'Asset Management View'],
        ],
        'contacts' => [
            'key' => 'modules.contacts_visible',
            'name' => 'Contacts',
            'icon' => 'bx bx-briefcase-alt-2',
            'roles' => ['Asset Management Admin', 'Asset Management Edit', 'Asset Management View'],
        ],
        'activities' => [
            'key' => 'modules.activities_visible',
            'name' => 'Activities Manager',
            'icon' => 'bx bx-run',
            'roles' => ['Activities Admin', 'Activities Edit', 'Activities View', 'Activities Staff'],
        ],
        'lms' => [
            'key' => 'modules.lms_visible',
            'name' => 'Learning Management System',
            'icon' => 'fas fa-graduation-cap',
            'roles' => ['LMS Admin', 'LMS Instructor', 'LMS Student'],
        ],
        'leave' => [
            'key' => 'modules.leave_visible',
            'name' => 'Leave Management',
            'icon' => 'bx bx-calendar-check',
            'roles' => ['Leave Admin', 'Leave View'],
        ],
        'fees' => [
            'key' => 'modules.fees_visible',
            'name' => 'Fee Administration',
            'icon' => 'bx bxs-wallet',
            'roles' => ['Fee Admin', 'Fee Collection', 'Fee Reports', 'Bursar'],
        ],
        'staff_attendance' => [
            'key' => 'modules.staff_attendance_visible',
            'name' => 'Staff Attendance',
            'icon' => 'bx bx-fingerprint',
            'roles' => ['HR Admin', 'Leave Admin'],
        ],
        'staff_pdp' => [
            'key' => 'modules.staff_pdp_visible',
            'name' => 'Staff PDP',
            'icon' => 'bx bx-spreadsheet',
            'roles' => ['PDP Admin'],
        ],
        'library' => [
            'key' => 'modules.library_visible',
            'name' => 'Library',
            'icon' => 'bx bx-book',
            'roles' => ['Librarian'],
        ],
        'timetable' => [
            'key' => 'modules.timetable_visible',
            'name' => 'Timetable',
            'icon' => 'bx bx-calendar-alt',
            'roles' => ['Academic Admin'],
        ],
        'invigilation' => [
            'key' => 'modules.invigilation_visible',
            'name' => 'Invigilation Roster',
            'icon' => 'bx bx-clipboard',
            'roles' => [],
        ],
        'schemes' => [
            'key' => 'modules.schemes_visible',
            'name' => 'Scheme of Work',
            'icon' => 'bx bx-book-open',
            'roles' => ['Teacher', 'HOD', 'Academic Admin'],
        ],
    ];

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function isModuleVisible(string $moduleKey): bool
    {
        if (!isset($this->modules[$moduleKey])) {
            return true;
        }

        return Cache::remember(
            self::CACHE_KEY . ':' . $moduleKey,
            self::CACHE_TTL,
            fn() => (bool) $this->settingsService->get($this->modules[$moduleKey]['key'], true)
        );
    }

    public function getVisibleModules(): array
    {
        return array_filter($this->modules, fn($key) => $this->isModuleVisible($key), ARRAY_FILTER_USE_KEY);
    }

    public function getLauncherModulesForUser(User $user): array
    {
        $gate = Gate::forUser($user);
        $launcherModules = [];

        foreach ($this->modules as $key => $module) {
            if (!$this->isModuleVisible($key)) {
                continue;
            }

            $url = $this->resolveLauncherUrlForUser($key, $user, $gate);

            if ($url === null) {
                continue;
            }

            $launcherModules[$key] = [
                'key' => $key,
                'label' => $module['name'],
                'icon' => $module['icon'],
                'url' => $url,
            ];
        }

        if ($gate->allows('access-setup')) {
            $settingsUrl = $this->routeIfExists('setup.school-setup');

            if ($settingsUrl !== null) {
                $launcherModules['settings'] = [
                    'key' => 'settings',
                    'label' => 'Settings',
                    'icon' => 'bx bx-slider-alt',
                    'url' => $settingsUrl,
                ];
            }
        }

        return $launcherModules;
    }

    public function getHiddenRoles(): array
    {
        $hiddenRoles = [];
        foreach ($this->modules as $key => $module) {
            if (!$this->isModuleVisible($key)) {
                $hiddenRoles = array_merge($hiddenRoles, $module['roles']);
            }
        }
        return array_unique($hiddenRoles);
    }

    public function getVisibleRoles($roles): Collection|array
    {
        $hiddenRoles = $this->getHiddenRoles();

        if ($roles instanceof Collection) {
            return $roles->filter(fn($role) => !in_array($role->name, $hiddenRoles));
        }

        return array_filter($roles, fn($role) => !in_array($role->name, $hiddenRoles));
    }

    public function getAllModules(): array
    {
        return $this->modules;
    }

    protected function resolveLauncherUrlForUser(string $moduleKey, User $user, GateContract $gate): ?string
    {
        return match ($moduleKey) {
            'leave' => $this->routeIfExists('leave.requests.index'),
            'staff_pdp' => app(\App\Services\Pdp\PdpAccessService::class)->hasElevatedAccess($user)
                ? $this->routeIfExists('staff.pdp.plans.index')
                : null,
            'staff_attendance' => $this->resolveStaffAttendanceLauncherUrl($user, $gate),
            'welfare' => $gate->allows('access-welfare')
                ? $this->routeIfExists('welfare.dashboard')
                : null,
            'schemes' => $gate->allows('access-schemes')
                ? $this->routeIfExists('schemes.index')
                : null,
            'communications' => $gate->allows('access-communications')
                ? $this->routeIfExists('notifications.index')
                : null,
            'lms' => $gate->allows('access-lms')
                ? $this->routeIfExists('lms.courses.index')
                : null,
            'assets' => $gate->allows('access-asset-management')
                ? $this->routeIfExists('assets.index')
                : null,
            'contacts' => $gate->allows('access-asset-management')
                ? $this->routeIfExists('contacts.index')
                : null,
            'activities' => $gate->allows('access-activities')
                ? $this->routeIfExists('activities.index')
                : null,
            'fees' => $this->resolveFeesLauncherUrl($gate),
            'library' => $this->resolveLibraryLauncherUrl($gate),
            'timetable' => $gate->allows('access-timetable')
                ? $this->routeIfExists('timetable.view.class')
                : null,
            'invigilation' => $gate->allows('access-invigilation')
                ? $this->routeIfExists('invigilation.index')
                : ($gate->allows('access-invigilation-published-roster')
                    ? $this->routeIfExists('invigilation.view.teacher-roster')
                    : null),
            default => null,
        };
    }

    protected function resolveStaffAttendanceLauncherUrl(User $user, GateContract $gate): ?string
    {
        if (($user->position === 'HOD' || $user->subordinates()->exists()) && Route::has('staff-attendance.manager.dashboard')) {
            return route('staff-attendance.manager.dashboard');
        }

        if ($gate->allows('staff-attendance-administration-access')) {
            return $this->routeIfExists('staff-attendance.manual-register.index');
        }

        return null;
    }

    protected function resolveFeesLauncherUrl(GateContract $gate): ?string
    {
        if (!$gate->allows('fee-administration-access')) {
            return null;
        }

        return $this->firstAccessibleRoute($gate, [
            ['name' => 'fees.reports.dashboard', 'gate' => 'view-fee-reports'],
            ['name' => 'fees.balance.outstanding', 'gate' => 'collect-fees'],
            ['name' => 'fees.setup.index', 'gate' => 'manage-fee-setup'],
        ]);
    }

    protected function resolveLibraryLauncherUrl(GateContract $gate): ?string
    {
        if (!$gate->allows('access-library')) {
            return null;
        }

        return $this->firstAccessibleRoute($gate, [
            ['name' => 'library.dashboard', 'gate' => 'manage-library'],
            ['name' => 'library.reservations.index', 'gate' => 'manage-library'],
            ['name' => 'library.inventory.index', 'gate' => 'manage-library'],
            ['name' => 'library.settings.index', 'gate' => 'manage-library-settings'],
        ]);
    }

    protected function firstAccessibleRoute(GateContract $gate, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (isset($candidate['gate']) && !$gate->allows($candidate['gate'])) {
                continue;
            }

            $url = $this->routeIfExists($candidate['name']);

            if ($url !== null) {
                return $url;
            }
        }

        return null;
    }

    protected function routeIfExists(string $routeName): ?string
    {
        if (!Route::has($routeName)) {
            return null;
        }

        return route($routeName);
    }

    public function getModuleVisibilityStatus(): array
    {
        $status = [];
        foreach ($this->modules as $key => $module) {
            $status[$key] = [
                'visible' => $this->isModuleVisible($key),
                'name' => $module['name'],
                'icon' => $module['icon'],
                'roles' => $module['roles'],
            ];
        }
        return $status;
    }

    public function updateModuleVisibility(string $moduleKey, bool $visible): bool
    {
        if (!isset($this->modules[$moduleKey])) {
            return false;
        }

        $module = $this->modules[$moduleKey];
        $settingKey = $module['key'];

        // Use updateOrCreate to handle both new and existing settings
        SMSApiSetting::updateOrCreate(
            ['key' => $settingKey],
            [
                'value' => $visible ? '1' : '0',
                'category' => 'modules',
                'type' => 'boolean',
                'description' => "Controls visibility of the {$module['name']} module",
                'display_name' => "{$module['name']} Visible",
                'is_editable' => true,
                'display_order' => 0,
            ]
        );

        // Clear caches
        Cache::forget(self::CACHE_KEY . ':' . $moduleKey);
        Cache::forget('system_setting:' . $settingKey);
        Cache::forget('system_settings:all');
        Cache::forget('system_settings:all:modules');

        return true;
    }

    public function clearCache(): void
    {
        foreach (array_keys($this->modules) as $key) {
            Cache::forget(self::CACHE_KEY . ':' . $key);
        }
    }
}

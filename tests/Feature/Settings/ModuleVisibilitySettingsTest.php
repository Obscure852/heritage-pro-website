<?php

namespace Tests\Feature\Settings;

use App\Models\Role;
use App\Models\User;
use App\Services\ModuleVisibilityService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\EnsuresPdpPhaseOneSchema;
use Tests\TestCase;

class ModuleVisibilitySettingsTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPdpPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensurePdpPhaseOneSchema();
        $this->ensureRolesTables();
        $this->ensureSchoolSetupTable();

        DB::table('school_setup')->updateOrInsert(
            ['id' => 1],
            [
                'school_name' => 'Merementsi Junior Secondary School',
                'type' => 'Junior',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function test_module_settings_page_lists_staff_pdp_module(): void
    {
        $user = $this->createUserWithRoles(
            'module-settings@example.com',
            ['System Setup'],
            ['position' => 'School Head']
        );

        $this->actingAs($user)
            ->get(route('setup.module-settings'))
            ->assertOk()
            ->assertSee('<h5>Staff PDP</h5>', false)
            ->assertSee('<h5>Activities Manager</h5>', false)
            ->assertSee('<h5>Contacts</h5>', false)
            ->assertSee('<h5>Invigilation</h5>', false);
    }

    public function test_invigilation_module_launcher_renders_for_authorized_user_when_enabled(): void
    {
        $user = $this->createUserWithRoles(
            'invigilation-launcher@example.com',
            ['System Setup', 'Academic Admin'],
            ['position' => 'Teacher']
        );

        $this->enableOnlyModules(['invigilation']);

        $this->actingAs($user)
            ->get(route('setup.module-settings'))
            ->assertOk()
            ->assertSee('<h5>Invigilation</h5>', false)
            ->assertSee('id="page-header-module-launcher-item-invigilation"', false)
            ->assertSee('<span data-key="t-invigilation">Invigilation Roster</span>', false);

        $launcherModules = app(ModuleVisibilityService::class)->getLauncherModulesForUser($user);

        $this->assertSame(route('invigilation.index'), $launcherModules['invigilation']['url']);
    }

    public function test_invigilation_settings_page_is_available_for_manage_users(): void
    {
        $user = $this->createUserWithRoles(
            'invigilation-settings@example.com',
            ['Academic Admin'],
            ['position' => 'Teacher']
        );

        $this->enableOnlyModules(['invigilation']);

        $this->actingAs($user)
            ->get(route('invigilation.settings.index'))
            ->assertOk()
            ->assertSee('Invigilation Settings')
            ->assertSee('Series Manager')
            ->assertSee('Daily Roster')
            ->assertSee('Conflict Report');
    }

    public function test_invigilation_module_launcher_routes_teachers_to_published_roster_view(): void
    {
        $user = $this->createUserWithRoles(
            'invigilation-teacher-launcher@example.com',
            ['Teacher'],
            ['position' => 'Teacher']
        );

        $this->enableOnlyModules(['invigilation']);

        $launcherModules = app(ModuleVisibilityService::class)->getLauncherModulesForUser($user);

        $this->assertSame(route('invigilation.view.teacher-roster'), $launcherModules['invigilation']['url']);
    }

    public function test_activities_module_launcher_renders_for_authorized_user_when_enabled(): void
    {
        $user = $this->createUserWithRoles(
            'activities-launcher@example.com',
            ['System Setup', 'Activities View'],
            ['position' => 'Teacher']
        );

        $this->enableOnlyModules(['activities']);

        $this->actingAs($user)
            ->get(route('setup.module-settings'))
            ->assertOk()
            ->assertSee('<h5>Activities Manager</h5>', false)
            ->assertSee('id="page-header-module-launcher-item-activities"', false);

        $launcherModules = app(ModuleVisibilityService::class)->getLauncherModulesForUser($user);

        $this->assertSame(route('activities.index'), $launcherModules['activities']['url']);
    }

    public function test_staff_pdp_and_lesson_plans_navigation_respect_module_visibility_settings(): void
    {
        $user = $this->createUserWithRoles(
            'module-navigation@example.com',
            ['System Setup', 'Teacher', 'Asset Management View'],
            ['position' => 'School Head']
        );
        $moduleVisibility = app(ModuleVisibilityService::class);

        $moduleVisibility->updateModuleVisibility('staff_pdp', true);
        $moduleVisibility->updateModuleVisibility('schemes', true);
        $moduleVisibility->updateModuleVisibility('contacts', true);

        $this->actingAs($user)
            ->get(route('setup.module-settings'))
            ->assertOk()
            ->assertSee('<span>My PDP</span>', false)
            ->assertSee('<span>Staff PDP</span>', false)
            ->assertSee('> Lesson Plans</a>', false)
            ->assertSee('<span data-key="t-contacts">Contacts</span>', false);

        $moduleVisibility->updateModuleVisibility('staff_pdp', false);
        $moduleVisibility->updateModuleVisibility('schemes', false);
        $moduleVisibility->updateModuleVisibility('contacts', false);

        $this->actingAs($user)
            ->get(route('setup.module-settings'))
            ->assertOk()
            ->assertDontSee('<span>My PDP</span>', false)
            ->assertDontSee('<span>Staff PDP</span>', false)
            ->assertDontSee('> Lesson Plans</a>', false)
            ->assertDontSee('<span data-key="t-contacts">Contacts</span>', false);
    }

    public function test_topbar_module_launcher_renders_only_accessible_enabled_modules(): void
    {
        $user = $this->createUserWithRoles(
            'module-launcher@example.com',
            ['System Setup', 'Teacher', 'Asset Management View'],
            ['position' => 'Teacher']
        );

        $this->enableOnlyModules(['staff_pdp', 'schemes', 'assets']);

        $this->actingAs($user)
            ->get(route('setup.module-settings'))
            ->assertOk()
            ->assertSee('id="page-header-module-launcher"', false)
            ->assertSee('id="page-header-module-launcher-item-schemes"', false)
            ->assertSee('id="page-header-module-launcher-item-assets"', false)
            ->assertSee('id="page-header-module-launcher-item-settings"', false)
            ->assertDontSee('id="page-header-module-launcher-item-staff_pdp"', false)
            ->assertDontSee('id="page-header-module-launcher-item-contacts"', false)
            ->assertDontSee('id="page-header-module-launcher-item-fees"', false);
    }

    public function test_topbar_module_launcher_keeps_settings_tile_for_setup_users(): void
    {
        $user = $this->createUserWithRoles(
            'module-launcher-settings@example.com',
            ['System Setup'],
            ['position' => 'School Head']
        );

        $this->enableOnlyModules(['assets']);

        $this->actingAs($user)
            ->get(route('setup.module-settings'))
            ->assertOk()
            ->assertSee('id="page-header-module-launcher"', false)
            ->assertSee('id="page-header-module-launcher-item-settings"', false)
            ->assertDontSee('id="page-header-module-launcher-item-assets"', false);
    }

    public function test_topbar_module_launcher_is_hidden_when_user_has_no_accessible_tiles(): void
    {
        $user = $this->createUserWithRoles(
            'module-launcher-hidden@example.com',
            ['Teacher'],
            ['position' => 'Teacher']
        );

        $this->enableOnlyModules(['assets']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('id="page-header-module-launcher"', false)
            ->assertDontSee('id="page-header-module-launcher-item-assets"', false)
            ->assertDontSee('id="page-header-module-launcher-item-settings"', false);
    }

    public function test_launcher_service_excludes_visible_modules_user_cannot_access(): void
    {
        $user = $this->createUserWithRoles(
            'module-launcher-service@example.com',
            ['Teacher'],
            ['position' => 'Teacher']
        );

        $this->enableOnlyModules(['assets', 'contacts']);

        $launcherModules = app(ModuleVisibilityService::class)->getLauncherModulesForUser($user);

        $this->assertSame([], $launcherModules);
    }

    public function test_launcher_service_resolves_expected_single_route_per_module(): void
    {
        $moduleVisibility = app(ModuleVisibilityService::class);

        $pdpUser = $this->createUserWithRoles(
            'launcher-pdp@example.com',
            ['Teacher'],
            ['position' => 'School Head']
        );
        $this->enableOnlyModules(['staff_pdp']);
        $pdpLauncherModules = $moduleVisibility->getLauncherModulesForUser($pdpUser);
        $this->assertSame(route('staff.pdp.plans.index'), $pdpLauncherModules['staff_pdp']['url']);

        $managerUser = $this->createUserWithRoles(
            'launcher-manager@example.com',
            ['Teacher'],
            ['position' => 'Teacher']
        );
        $this->createUserWithRoles(
            'launcher-subordinate@example.com',
            ['Teacher'],
            ['reporting_to' => $managerUser->id]
        );
        $this->enableOnlyModules(['staff_attendance']);
        $managerLauncherModules = $moduleVisibility->getLauncherModulesForUser($managerUser);
        $this->assertSame(route('staff-attendance.manager.dashboard'), $managerLauncherModules['staff_attendance']['url']);

        $attendanceAdminUser = $this->createUserWithRoles(
            'launcher-attendance-admin@example.com',
            ['HR Admin'],
            ['position' => 'Teacher']
        );
        $attendanceAdminLauncherModules = $moduleVisibility->getLauncherModulesForUser($attendanceAdminUser);
        $this->assertSame(route('staff-attendance.manual-register.index'), $attendanceAdminLauncherModules['staff_attendance']['url']);

        $feesUser = $this->createUserWithRoles(
            'launcher-fees@example.com',
            ['Fee Admin'],
            ['position' => 'Teacher']
        );
        $this->enableOnlyModules(['fees']);
        $feesLauncherModules = $moduleVisibility->getLauncherModulesForUser($feesUser);
        $this->assertSame(route('fees.reports.dashboard'), $feesLauncherModules['fees']['url']);

        $libraryUser = $this->createUserWithRoles(
            'launcher-library@example.com',
            ['Librarian'],
            ['position' => 'Teacher']
        );
        $this->enableOnlyModules(['library']);
        $libraryLauncherModules = $moduleVisibility->getLauncherModulesForUser($libraryUser);
        $this->assertSame(route('library.dashboard'), $libraryLauncherModules['library']['url']);
    }

    private function ensureRolesTables(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_users')) {
            Schema::create('role_users', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->timestamps();
            });
        }
    }

    private function ensureSchoolSetupTable(): void
    {
        if (Schema::hasTable('school_setup')) {
            return;
        }

        Schema::create('school_setup', function (Blueprint $table): void {
            $table->id();
            $table->string('school_name')->nullable();
            $table->string('school_id')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function createUserWithRoles(string $email, array $roles, array $overrides = []): User
    {
        $user = User::withoutEvents(fn () => User::query()->create(array_merge([
            'firstname' => 'Module',
            'lastname' => 'Admin',
            'email' => $email,
            'password' => 'secret',
            'status' => 'Current',
            'position' => 'Teacher',
            'year' => 2026,
        ], $overrides)));

        $roleIds = collect($roles)
            ->map(fn (string $name): int => (int) Role::query()->firstOrCreate(
                ['name' => $name],
                ['description' => $name]
            )->id)
            ->all();

        $user->roles()->syncWithoutDetaching($roleIds);

        return $user->fresh();
    }

    private function enableOnlyModules(array $visibleModules): void
    {
        $moduleVisibility = app(ModuleVisibilityService::class);

        foreach (array_keys($moduleVisibility->getAllModules()) as $moduleKey) {
            $moduleVisibility->updateModuleVisibility($moduleKey, in_array($moduleKey, $visibleModules, true));
        }

        $moduleVisibility->clearCache();
    }
}

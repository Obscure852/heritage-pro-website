<?php

namespace Tests\Feature\Staff;

use App\Helpers\CacheHelper;
use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Department;
use App\Models\Nationality;
use App\Models\QualificationUser;
use App\Models\Qualification;
use App\Models\SchoolSetup;
use App\Models\StaffProfileSetting;
use App\Models\User;
use App\Models\WorkHistory;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\EnsuresStaffProfileSchema;
use Tests\TestCase;
use App\Http\Middleware\BlockNonAfricanCountries;

class StaffProfileFieldsTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresStaffProfileSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureStaffProfileSchema();
        $this->seedReferenceData();
    }

    public function test_create_page_renders_new_pdp_profile_fields(): void
    {
        $admin = $this->createUser('staff-create-admin@example.com');

        $this->withoutMiddleware()
            ->actingAs($admin)
            ->get(route('staff.staff-new'))
            ->assertOk()
            ->assertSee('Reporting To')
            ->assertSee('Personal Payroll Number')
            ->assertSee('DPSM Personal File No')
            ->assertSee('Date of Appointment')
            ->assertSee('Grade (Earning Band)')
            ->assertSee('B4');
    }

    public function test_store_and_update_persist_new_staff_profile_fields(): void
    {
        $admin = $this->createUser('staff-save-admin@example.com');

        $createResponse = $this->withoutMiddleware()
            ->actingAs($admin)
            ->post(route('staff.staff-create'), [
                'firstname' => 'Thato',
                'middlename' => 'Neo',
                'lastname' => 'Buseng',
                'email' => 'new-staff@example.com',
                'date_of_birth' => '1990-05-05',
                'gender' => 'F',
                'department' => 'Academics',
                'position' => 'Teacher',
                'reporting_to' => '',
                'area_of_work' => 'Teaching',
                'personal_payroll_number' => 'PPN-1001',
                'dpsm_personal_file_number' => '81716',
                'date_of_appointment' => '2020-01-15',
                'earning_band' => 'B4',
                'nationality' => 'Motswana',
                'phone' => '71234567',
                'id_number' => '111111111',
                'status' => 'Current',
                'username' => 'thatob',
                'last_updated_by' => $admin->id,
                'year' => 2026,
                'bypass_duplicate_check' => 1,
            ]);

        $user = User::query()->where('email', 'new-staff@example.com')->firstOrFail();

        $createResponse->assertRedirect(route('staff.staff-view', $user->id));
        $this->assertNull($user->reporting_to);
        $this->assertSame('PPN-1001', $user->personal_payroll_number);
        $this->assertSame('81716', $user->dpsm_personal_file_number);
        $this->assertSame('2020-01-15', optional($user->date_of_appointment)->format('Y-m-d'));
        $this->assertSame('B4', $user->earning_band);

        $this->withoutMiddleware()
            ->actingAs($admin)
            ->get(route('staff.staff-view', $user->id))
            ->assertOk()
            ->assertSee('Personal Payroll Number')
            ->assertSee('DPSM Personal File No')
            ->assertSee('Date of Appointment')
            ->assertSee('Grade (Earning Band)');

        $updateResponse = $this->withoutMiddleware()
            ->actingAs($admin)
            ->from(route('staff.staff-view', $user->id))
            ->post(route('staff.staff-update', $user->id), [
                'firstname' => 'Thato',
                'middlename' => 'Neo',
                'lastname' => 'Buseng',
                'email' => 'new-staff@example.com',
                'date_of_birth' => '1990-05-05',
                'gender' => 'F',
                'department' => 'Academics',
                'position' => 'Senior Teacher',
                'area_of_work' => 'Teaching',
                'personal_payroll_number' => 'PPN-2002',
                'dpsm_personal_file_number' => '99112',
                'date_of_appointment' => '2021-02-20',
                'earning_band' => 'C2',
                'nationality' => 'Motswana',
                'phone' => '71234567',
                'id_number' => '111111111',
                'city' => 'Gaborone',
                'address' => 'Plot 123',
                'status' => 'Current',
                'user_filter_id' => '',
                'reporting_to' => (string) $admin->id,
                'last_updated_by' => $admin->id,
                'year' => 2026,
            ]);

        $updateResponse->assertRedirect(route('staff.staff-view', $user->id));

        $user->refresh();

        $this->assertSame('PPN-2002', $user->personal_payroll_number);
        $this->assertSame('99112', $user->dpsm_personal_file_number);
        $this->assertSame('2021-02-20', optional($user->date_of_appointment)->format('Y-m-d'));
        $this->assertSame('C2', $user->earning_band);
    }

    public function test_profile_page_renders_and_updates_new_staff_profile_fields(): void
    {
        $user = $this->createUser('staff-profile-self@example.com', [
            'firstname' => 'Neo',
            'lastname' => 'Matshoga',
            'email' => 'staff-profile-self@example.com',
            'date_of_birth' => '1990-05-05',
            'personal_payroll_number' => 'PPN-3003',
            'dpsm_personal_file_number' => '81716',
            'date_of_appointment' => '2020-03-01',
            'earning_band' => 'B4',
        ]);

        $this->withoutMiddleware([
                Authorize::class,
                BlockNonAfricanCountries::class,
                ThrottleRequests::class,
            ])
            ->actingAs($user)
            ->get(route('staff.profile'))
            ->assertOk()
            ->assertSee('Personal Payroll Number')
            ->assertSee('DPSM Personal File No')
            ->assertSee('Date of Appointment')
            ->assertSee('Grade (Earning Band)')
            ->assertSee('PPN-3003')
            ->assertSee('81716')
            ->assertSee('B4');

        $this->withoutMiddleware([
                Authorize::class,
                BlockNonAfricanCountries::class,
                ThrottleRequests::class,
            ])
            ->actingAs($user)
            ->from(route('staff.profile'))
            ->put(route('users.update-profile-details', $user), [
                'firstname' => 'Neo',
                'lastname' => 'Matshoga',
                'date_of_birth' => '1990-05-05',
                'id_number' => '1234567890',
                'email' => 'staff-profile-self-updated@example.com',
                'phone' => '72345678',
                'nationality' => 'Motswana',
                'address' => 'Plot 123',
                'personal_payroll_number' => 'PPN-4004',
                'dpsm_personal_file_number' => '99112',
                'date_of_appointment' => '2021-04-15',
                'earning_band' => 'C2',
            ])
            ->assertRedirect(route('staff.profile'));

        $user->refresh();

        $this->assertSame('staff-profile-self-updated@example.com', $user->email);
        $this->assertSame('PPN-4004', $user->personal_payroll_number);
        $this->assertSame('99112', $user->dpsm_personal_file_number);
        $this->assertSame('2021-04-15', optional($user->date_of_appointment)->format('Y-m-d'));
        $this->assertSame('C2', $user->earning_band);
    }

    public function test_store_rejects_unknown_earning_band_when_configured_options_exist(): void
    {
        $admin = $this->createUser('staff-invalid-band-admin@example.com');

        $this->withoutMiddleware()
            ->actingAs($admin)
            ->from(route('staff.staff-new'))
            ->post(route('staff.staff-create'), [
                'firstname' => 'Invalid',
                'lastname' => 'Band',
                'email' => 'invalid-band@example.com',
                'date_of_birth' => '1990-05-05',
                'gender' => 'F',
                'department' => 'Academics',
                'position' => 'Teacher',
                'area_of_work' => 'Teaching',
                'personal_payroll_number' => 'PPN-9999',
                'dpsm_personal_file_number' => '70001',
                'date_of_appointment' => '2022-01-01',
                'earning_band' => 'Z9',
                'nationality' => 'Motswana',
                'phone' => '71234567',
                'id_number' => '222222222',
                'status' => 'Current',
                'username' => 'invalidband',
                'last_updated_by' => $admin->id,
                'year' => 2026,
                'bypass_duplicate_check' => 1,
            ])
            ->assertRedirect(route('staff.staff-new'))
            ->assertSessionHasErrors('earning_band');
    }

    public function test_force_profile_update_setting_can_be_toggled(): void
    {
        StaffProfileSetting::set(StaffProfileSetting::KEY_ENABLED, false);
        $this->assertFalse(StaffProfileSetting::isForceUpdateEnabled());

        StaffProfileSetting::set(StaffProfileSetting::KEY_ENABLED, true);
        $this->assertTrue(StaffProfileSetting::isForceUpdateEnabled());

        StaffProfileSetting::set(StaffProfileSetting::KEY_SECTIONS, ['basic_info', 'qualifications']);
        $this->assertEquals(['basic_info', 'qualifications'], StaffProfileSetting::getRequiredSections());
    }

    public function test_middleware_redirects_incomplete_user_when_enabled(): void
    {
        StaffProfileSetting::set(StaffProfileSetting::KEY_ENABLED, true);
        StaffProfileSetting::set(StaffProfileSetting::KEY_SECTIONS, ['basic_info']);
        Cache::forget('force_profile_update_enabled');

        $user = $this->createUser('incomplete-user@example.com', [
            'date_of_birth' => null,
        ]);

        $this->withoutMiddleware([
                Authorize::class,
                BlockNonAfricanCountries::class,
                ThrottleRequests::class,
            ])
            ->actingAs($user)
            ->get(route('staff.index'))
            ->assertRedirect(route('profile.complete'));
    }

    public function test_middleware_allows_complete_user_through(): void
    {
        StaffProfileSetting::set(StaffProfileSetting::KEY_ENABLED, true);
        StaffProfileSetting::set(StaffProfileSetting::KEY_SECTIONS, ['basic_info']);
        Cache::forget('force_profile_update_enabled');

        $user = $this->createUser('complete-user@example.com', [
            'firstname' => 'Complete',
            'lastname' => 'User',
            'date_of_birth' => '1990-01-01',
            'id_number' => '123456789',
            'email' => 'complete-user@example.com',
            'nationality' => 'Motswana',
        ]);

        $this->withoutMiddleware([
                Authorize::class,
                BlockNonAfricanCountries::class,
                ThrottleRequests::class,
            ])
            ->actingAs($user)
            ->get(route('staff.index'))
            ->assertOk();
    }

    public function test_middleware_allows_admin_through_even_if_incomplete(): void
    {
        StaffProfileSetting::set(StaffProfileSetting::KEY_ENABLED, true);
        StaffProfileSetting::set(StaffProfileSetting::KEY_SECTIONS, ['basic_info']);
        Cache::forget('force_profile_update_enabled');

        $admin = $this->createUser('admin-incomplete@example.com', [
            'date_of_birth' => null,
        ]);

        DB::table('roles')->insertOrIgnore(['id' => 99, 'name' => 'Administrator']);
        DB::table('role_users')->insert([
            'user_id' => $admin->id,
            'role_id' => 99,
        ]);

        $this->withoutMiddleware([
                Authorize::class,
                BlockNonAfricanCountries::class,
                ThrottleRequests::class,
            ])
            ->actingAs($admin)
            ->get(route('staff.index'))
            ->assertOk();
    }

    public function test_complete_profile_page_shows_required_sections(): void
    {
        StaffProfileSetting::set(StaffProfileSetting::KEY_ENABLED, true);
        StaffProfileSetting::set(StaffProfileSetting::KEY_SECTIONS, ['basic_info', 'employment_details']);
        Cache::forget('force_profile_update_enabled');

        $user = $this->createUser('show-sections@example.com', [
            'date_of_birth' => null,
            'personal_payroll_number' => null,
        ]);

        $this->withoutMiddleware([
                Authorize::class,
                BlockNonAfricanCountries::class,
                ThrottleRequests::class,
            ])
            ->actingAs($user)
            ->get(route('profile.complete'))
            ->assertOk()
            ->assertSee('Basic Information')
            ->assertSee('Employment Details')
            ->assertSee('Date of Birth')
            ->assertSee('Personal Payroll Number');
    }

    public function test_complete_profile_blocks_until_qualifications_added(): void
    {
        StaffProfileSetting::set(StaffProfileSetting::KEY_ENABLED, true);
        StaffProfileSetting::set(StaffProfileSetting::KEY_SECTIONS, ['qualifications']);
        Cache::forget('force_profile_update_enabled');

        $user = $this->createUser('no-quals@example.com');

        // Should be redirected (no qualifications)
        $this->withoutMiddleware([
                Authorize::class,
                BlockNonAfricanCountries::class,
                ThrottleRequests::class,
            ])
            ->actingAs($user)
            ->get(route('staff.index'))
            ->assertRedirect(route('profile.complete'));

        // Add a qualification
        $qual = Qualification::first() ?? Qualification::create([
            'qualification' => 'BSc Education',
            'qualification_code' => 'BSC-ED',
        ]);
        QualificationUser::create([
            'user_id' => $user->id,
            'qualification_id' => $qual->id,
            'level' => 'Degree',
            'college' => 'University of Botswana',
            'start_date' => '2010-01-01',
            'completion_date' => '2014-12-31',
        ]);

        // Simulate "Save & Complete" — call the check endpoint to clear the session gate
        $this->withoutMiddleware([
                Authorize::class,
                BlockNonAfricanCountries::class,
                ThrottleRequests::class,
            ])
            ->actingAs($user)
            ->getJson(route('profile.complete.check'))
            ->assertJson(['complete' => true]);

        // Should pass through now
        $this->withoutMiddleware([
                Authorize::class,
                BlockNonAfricanCountries::class,
                ThrottleRequests::class,
            ])
            ->actingAs($user)
            ->get(route('staff.index'))
            ->assertOk();
    }

    public function test_complete_profile_save_allows_dashboard_access(): void
    {
        StaffProfileSetting::set(StaffProfileSetting::KEY_ENABLED, true);
        StaffProfileSetting::set(StaffProfileSetting::KEY_SECTIONS, ['basic_info']);
        Cache::forget('force_profile_update_enabled');

        $user = $this->createUser('save-complete@example.com', [
            'date_of_birth' => null,
        ]);

        // Save the missing field
        $this->withoutMiddleware()
            ->actingAs($user)
            ->post(route('profile.complete.save'), [
                'date_of_birth' => '1990-05-05',
            ])
            ->assertRedirect(route('profile.complete'));

        $user->refresh();
        $this->assertSame('1990-05-05', optional($user->date_of_birth)->format('Y-m-d') ?? $user->date_of_birth);

        // Verify the user is now considered complete
        $incomplete = StaffProfileSetting::getIncompleteItems($user);
        $this->assertEmpty($incomplete['missing_fields']);
        $this->assertEmpty($incomplete['missing_sections']);
    }

    private function seedReferenceData(): void
    {
        Cache::forget('nationalities');
        Cache::forget('departments');
        Cache::forget('user_filters');
        CacheHelper::forgetStaff();

        SchoolSetup::create([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);

        Department::create(['name' => 'Academics']);
        Nationality::create(['name' => 'Motswana']);

        DB::table('area_of_work')->insert([
            'category' => 'Academic',
            'name' => 'Teaching',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('user_positions')->insert([
            ['name' => 'Teacher', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Senior Teacher', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'School Head', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('users_status')->insert([
            ['name' => 'Current', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Left', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function createUser(string $email, array $overrides = []): User
    {
        return User::withoutEvents(fn () => User::query()->create(array_merge([
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => $email,
            'password' => 'secret',
            'status' => 'Current',
            'position' => 'School Head',
            'department' => 'Academics',
            'area_of_work' => 'Teaching',
            'nationality' => 'Motswana',
            'phone' => '70123456',
            'date_of_birth' => '1990-01-01',
            'id_number' => uniqid('id-', true),
            'year' => 2026,
        ], $overrides)));
    }
}

<?php

namespace Tests\Feature\Crm;

use App\Models\CrmCommercialSetting;
use App\Models\CrmUserDepartment;
use App\Models\CrmUserFilter;
use App\Models\CrmUserPosition;
use App\Models\User;
use App\Services\Crm\CrmModulePermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CrmUserWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_update_users_with_extended_profile_fields_and_filters(): void
    {
        Storage::fake('public');

        $admin = $this->createUser([
            'email' => 'admin-users@example.com',
            'role' => 'admin',
        ]);

        $manager = $this->createUser([
            'email' => 'manager-users@example.com',
            'role' => 'manager',
            'name' => 'Reporting Manager',
        ]);

        $department = CrmUserDepartment::query()->create([
            'name' => 'Admissions',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $updatedDepartment = CrmUserDepartment::query()->create([
            'name' => 'Operations',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $position = CrmUserPosition::query()->create([
            'name' => 'Officer',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $updatedPosition = CrmUserPosition::query()->create([
            'name' => 'Coordinator',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $filterA = CrmUserFilter::query()->create([
            'name' => 'Head Office',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $filterB = CrmUserFilter::query()->create([
            'name' => 'Onboarding',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('crm.users.store'), [
                'name' => 'Profiled Staff User',
                'email' => 'profiled.staff@example.com',
                'phone' => '+267 7111 2222',
                'id_number' => 'OMANG-1001',
                'date_of_birth' => '1991-03-22',
                'gender' => 'female',
                'nationality' => 'Botswana',
                'employment_status' => 'active',
                'department_id' => $department->id,
                'position_id' => $position->id,
                'reports_to_user_id' => $manager->id,
                'date_of_appointment' => '2024-01-15',
                'role' => 'rep',
                'active' => '1',
                'personal_payroll_number' => 'PAY-1001',
                'custom_filter_ids' => [$filterA->id, $filterB->id],
                'avatar_cropped_image' => $this->croppedImagePayload(),
            ]);

        $user = User::query()->where('email', 'profiled.staff@example.com')->firstOrFail();

        $response->assertRedirect(route('crm.users.edit', ['user' => $user, 'tab' => 'profile']));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'department_id' => $department->id,
            'position_id' => $position->id,
            'reports_to_user_id' => $manager->id,
            'employment_status' => 'active',
            'personal_payroll_number' => 'PAY-1001',
        ]);
        $this->assertDatabaseHas('crm_user_filter_user', [
            'user_id' => $user->id,
            'crm_user_filter_id' => $filterA->id,
        ]);
        $this->assertDatabaseHas('crm_user_filter_user', [
            'user_id' => $user->id,
            'crm_user_filter_id' => $filterB->id,
        ]);
        Storage::disk('public')->assertExists($user->fresh()->avatar_path);

        $this->actingAs($admin)
            ->patch(route('crm.users.update', $user), [
                'name' => 'Profiled Staff User Updated',
                'email' => 'profiled.staff@example.com',
                'role' => 'rep',
                'active' => '1',
                'date_of_birth' => '1991-03-22',
                'gender' => 'female',
                'nationality' => 'Botswana',
                'id_number' => 'OMANG-1001',
                'phone' => '+267 7333 4444',
                'employment_status' => 'on_leave',
                'department_id' => $updatedDepartment->id,
                'position_id' => $updatedPosition->id,
                'reports_to_user_id' => $admin->id,
                'personal_payroll_number' => 'PAY-2002',
                'date_of_appointment' => '2024-02-01',
                'custom_filter_ids' => [$filterB->id],
            ])
            ->assertRedirect(route('crm.users.edit', ['user' => $user, 'tab' => 'profile']));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Profiled Staff User Updated',
            'phone' => '+267 7333 4444',
            'employment_status' => 'on_leave',
            'department_id' => $updatedDepartment->id,
            'position_id' => $updatedPosition->id,
            'reports_to_user_id' => $admin->id,
            'personal_payroll_number' => 'PAY-2002',
        ]);
        $this->assertDatabaseMissing('crm_user_filter_user', [
            'user_id' => $user->id,
            'crm_user_filter_id' => $filterA->id,
        ]);
        $this->assertDatabaseHas('crm_user_filter_user', [
            'user_id' => $user->id,
            'crm_user_filter_id' => $filterB->id,
        ]);
    }

    public function test_admin_can_manage_users_settings_workspace(): void
    {
        Storage::fake('public');

        $admin = $this->createUser();

        $this->actingAs($admin)
            ->get(route('crm.users.settings.departments'))
            ->assertOk()
            ->assertSee('Current departments')
            ->assertDontSee('Company information');

        $this->actingAs($admin)
            ->get(route('crm.settings.company-information'))
            ->assertOk()
            ->assertSee('Business profile');

        $this->actingAs($admin)
            ->get(route('crm.settings.branding'))
            ->assertOk()
            ->assertSee('Logo and login image');

        $this->actingAs($admin)
            ->post(route('crm.users.settings.departments.store'), [
                'name' => 'Finance Office',
                'sort_order' => 3,
                'is_active' => '1',
            ])
            ->assertRedirect(route('crm.users.settings.departments'));

        $department = CrmUserDepartment::query()->where('name', 'Finance Office')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('crm.users.settings.departments.update', $department), [
                'name' => 'Finance and Admin',
                'sort_order' => 4,
                'is_active' => '1',
            ])
            ->assertRedirect(route('crm.users.settings.departments'));

        $this->actingAs($admin)
            ->post(route('crm.users.settings.positions.store'), [
                'name' => 'Bursar',
                'sort_order' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect(route('crm.users.settings.positions'));

        $this->actingAs($admin)
            ->post(route('crm.users.settings.filters.store'), [
                'name' => 'Field Team',
                'sort_order' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect(route('crm.users.settings.filters'));

        $filter = CrmUserFilter::query()->where('name', 'Field Team')->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('crm.users.settings.filters.destroy', $filter))
            ->assertRedirect(route('crm.users.settings.filters'));

        $this->actingAs($admin)
            ->patch(route('crm.settings.company-information.update'), [
                'company_name' => 'Imagelife CRM',
                'company_email' => 'hello@imagelife.co',
                'company_phone' => '+267 7000 0000',
                'company_website' => 'https://imagelife.co',
                'company_address_line_1' => 'Plot 12',
                'company_address_line_2' => 'Innovation Hub',
                'company_city' => 'Gaborone',
                'company_state' => 'South East',
                'company_country' => 'Botswana',
                'company_postal_code' => '0000',
            ])
            ->assertRedirect(route('crm.settings.company-information'));

        $this->actingAs($admin)
            ->patch(route('crm.settings.branding.update'), [
                'company_logo_cropped_image' => $this->croppedImagePayload(),
                'login_image_cropped_image' => $this->croppedImagePayload(),
            ])
            ->assertRedirect(route('crm.settings.branding'));

        $settings = CrmCommercialSetting::query()->firstOrFail();

        $this->assertDatabaseHas('crm_user_departments', [
            'id' => $department->id,
            'name' => 'Finance and Admin',
            'sort_order' => 4,
        ]);
        $this->assertDatabaseHas('crm_user_positions', [
            'name' => 'Bursar',
        ]);
        $this->assertDatabaseMissing('crm_user_filters', [
            'id' => $filter->id,
        ]);
        $this->assertDatabaseHas('crm_commercial_settings', [
            'id' => $settings->id,
            'company_name' => 'Imagelife CRM',
            'company_email' => 'hello@imagelife.co',
            'company_phone' => '+267 7000 0000',
            'company_city' => 'Gaborone',
        ]);
        Storage::disk('public')->assertExists($settings->company_logo_path);
        Storage::disk('public')->assertExists($settings->login_image_path);
    }

    public function test_admin_can_manage_qualifications_and_signatures(): void
    {
        Storage::fake('documents');

        $admin = $this->createUser();
        $staff = $this->createUser([
            'email' => 'staff-docs@example.com',
            'role' => 'rep',
        ]);

        $this->actingAs($admin)
            ->post(route('crm.users.qualifications.store', $staff), [
                'title' => 'BSc Computer Science',
                'level' => 'Degree',
                'institution' => 'University of Botswana',
                'start_date' => '2012-01-10',
                'completion_date' => '2015-11-30',
                'notes' => 'Graduated with distinction',
                'attachments' => [
                    UploadedFile::fake()->create('certificate.pdf', 180, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('crm.users.edit', ['user' => $staff, 'tab' => 'qualifications']));

        $qualification = $staff->qualifications()->firstOrFail();
        $attachment = $qualification->attachments()->firstOrFail();

        Storage::disk('documents')->assertExists($attachment->path);

        $this->actingAs($admin)
            ->get(route('crm.users.qualifications.attachments.open', [$staff, $qualification, $attachment]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.users.qualifications.attachments.download', [$staff, $qualification, $attachment]))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('crm.users.signatures.store', $staff), [
                'label' => 'Main signature',
                'file' => UploadedFile::fake()->image('signature-one.png'),
            ])
            ->assertRedirect(route('crm.users.edit', ['user' => $staff, 'tab' => 'settings']));

        $this->actingAs($admin)
            ->post(route('crm.users.signatures.store', $staff), [
                'label' => 'Alternate signature',
                'file' => UploadedFile::fake()->image('signature-two.png'),
            ])
            ->assertRedirect(route('crm.users.edit', ['user' => $staff, 'tab' => 'settings']));

        $defaultSignature = $staff->fresh()->signatures()->where('label', 'Main signature')->firstOrFail();
        $alternateSignature = $staff->signatures()->where('label', 'Alternate signature')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('crm.users.signatures.default', [$staff, $alternateSignature]))
            ->assertRedirect(route('crm.users.edit', ['user' => $staff, 'tab' => 'settings']));

        $this->actingAs($admin)
            ->get(route('crm.users.signatures.open', [$staff, $alternateSignature]))
            ->assertOk();

        $this->actingAs($admin)
            ->delete(route('crm.users.signatures.destroy', [$staff, $defaultSignature]))
            ->assertRedirect(route('crm.users.edit', ['user' => $staff, 'tab' => 'settings']));

        $this->assertDatabaseHas('crm_user_signatures', [
            'id' => $alternateSignature->id,
            'is_default' => true,
        ]);
        $this->assertDatabaseMissing('crm_user_signatures', [
            'id' => $defaultSignature->id,
        ]);
    }

    public function test_login_and_logout_events_are_recorded(): void
    {
        $user = $this->createUser([
            'email' => 'event-user@example.com',
            'role' => 'rep',
            'active' => true,
        ]);

        $this->post(route('login'), [
            'email' => 'event-user@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('crm.dashboard'));

        $this->assertDatabaseHas('crm_user_login_events', [
            'user_id' => $user->id,
            'event_type' => 'login',
        ]);

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('crm_user_login_events', [
            'user_id' => $user->id,
            'event_type' => 'logout',
        ]);
    }

    public function test_explicit_module_permissions_override_role_defaults_for_users_module_and_finance_falls_back_to_commercial_settings(): void
    {
        $service = app(CrmModulePermissionService::class);

        $rep = $this->createUser([
            'email' => 'permissions-rep@example.com',
            'role' => 'rep',
        ]);

        $this->actingAs($rep)
            ->get(route('crm.users.index'))
            ->assertForbidden();

        $service->syncPermissions($rep, [
            'dashboard' => 'view',
            'users' => 'view',
        ]);

        $this->actingAs($rep)
            ->get(route('crm.users.index'))
            ->assertOk()
            ->assertSee('CRM staff directory')
            ->assertDontSee(route('crm.users.settings.index'), false);

        $this->actingAs($rep)
            ->get(route('crm.users.settings.index'))
            ->assertForbidden();

        $finance = $this->createUser([
            'email' => 'finance-settings@example.com',
            'role' => 'finance',
        ]);

        $this->actingAs($finance)
            ->get(route('crm.settings.index'))
            ->assertRedirect(route('crm.settings.commercial'));

        $this->actingAs($finance)
            ->get(route('crm.settings.commercial'))
            ->assertOk();
    }

    private function createUser(array $attributes = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'CRM User',
            'email' => 'user-' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'active' => true,
        ], $attributes));
    }

    private function croppedImagePayload(): string
    {
        $canvas = imagecreatetruecolor(24, 24);
        $background = imagecolorallocate($canvas, 59, 130, 246);
        imagefill($canvas, 0, 0, $background);

        ob_start();
        imagepng($canvas);
        $binary = ob_get_clean();
        imagedestroy($canvas);

        return 'data:image/png;base64,' . base64_encode($binary);
    }
}

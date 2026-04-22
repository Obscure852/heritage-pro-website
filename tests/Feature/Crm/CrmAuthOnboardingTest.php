<?php

namespace Tests\Feature\Crm;

use App\Models\CrmCommercialSetting;
use App\Models\CrmUserDepartment;
use App\Models\CrmUserPosition;
use App\Models\User;
use App\Services\Crm\CrmModulePermissionService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CrmAuthOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_pages_render_the_new_branded_shell(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in to the CRM')
            ->assertDontSee('Register');

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Create or reset your password');

        $this->get(route('password.reset', ['token' => 'test-token']))
            ->assertOk()
            ->assertSee('Choose your password');
    }

    public function test_password_reset_request_sends_a_reset_notification_for_existing_crm_users(): void
    {
        Notification::fake();

        $user = $this->createUser([
            'email' => 'reset-user@example.com',
        ]);

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_reset_notification_uses_the_branded_crm_email_view(): void
    {
        CrmCommercialSetting::query()->first()->update([
            'company_name' => 'Heritage Pro',
            'company_email' => 'support@heritagepro.test',
            'company_phone' => '+267 390 0000',
            'company_website' => 'heritagepro.test',
            'company_logo_path' => 'branding/logo/email-logo.jpg',
            'login_image_path' => 'branding/login/email-login.jpg',
        ]);

        $user = $this->createUser([
            'name' => 'Reset Recipient',
            'email' => 'mail-render@example.com',
        ]);

        $token = 'crm-reset-token';
        $mailMessage = (new ResetPassword($token))->toMail($user);
        $rendered = $mailMessage->render();
        $expectedResetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], false));

        $this->assertSame([
            'html' => 'emails.auth.reset-password',
            'text' => 'emails.auth.reset-password-text',
        ], $mailMessage->view);
        $this->assertSame('Create or reset your password for Heritage Pro', $mailMessage->subject);
        $this->assertStringContainsString('Create or reset your CRM password', $rendered);
        $this->assertStringNotContainsString('<img ', $rendered);
        $this->assertStringContainsString($expectedResetUrl, $rendered);
        $this->assertStringContainsString('support@heritagepro.test', $rendered);
        $this->assertStringContainsString('padding:28px 28px 0;', $rendered);
    }

    public function test_password_reset_redirects_a_first_time_user_into_onboarding(): void
    {
        $user = $this->createUser([
            'email' => 'first-time@example.com',
        ]);

        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('crm.onboarding.profile'));

        $user->refresh();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->requiresCrmOnboarding());
        $this->assertSame(1, $user->crm_onboarding_step);
        $this->assertNull($user->crm_onboarded_at);
    }

    public function test_pending_onboarding_users_are_redirected_from_the_dashboard(): void
    {
        $user = $this->createPendingOnboardingUser(step: 1);

        $this->actingAs($user)
            ->get(route('crm.dashboard'))
            ->assertRedirect(route('crm.onboarding.profile'));

        $user->forceFill([
            'crm_onboarding_step' => 2,
        ])->save();

        $this->actingAs($user)
            ->get(route('crm.dashboard'))
            ->assertRedirect(route('crm.onboarding.work'));
    }

    public function test_onboarding_profile_step_saves_identity_details_and_advances_to_work_setup(): void
    {
        $user = $this->createPendingOnboardingUser(attributes: [
            'email' => 'profile-step@example.com',
        ]);

        $this->actingAs($user)
            ->patch(route('crm.onboarding.profile.update'), [
                'email' => 'profile-step@example.com',
                'name' => 'Profile Step User',
                'phone' => '71000001',
                'id_number' => 'ID-7788',
                'date_of_birth' => '1993-04-12',
                'gender' => 'male',
                'nationality' => 'Motswana',
            ])->assertRedirect(route('crm.onboarding.work'));

        $user->refresh();

        $this->assertSame(2, $user->crm_onboarding_step);
        $this->assertSame('71000001', $user->phone);
        $this->assertSame('ID-7788', $user->id_number);
        $this->assertSame('male', $user->gender);
        $this->assertSame('Motswana', $user->nationality);
    }

    public function test_onboarding_work_step_completes_setup_and_redirects_to_the_intended_crm_page(): void
    {
        $manager = $this->createUser([
            'email' => 'manager@example.com',
            'name' => 'Manager User',
        ]);

        $department = CrmUserDepartment::query()->create([
            'name' => 'Administration',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $position = CrmUserPosition::query()->create([
            'name' => 'Teacher',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $user = $this->createPendingOnboardingUser(step: 2, attributes: [
            'email' => 'work-step@example.com',
        ]);

        $this->actingAs($user)
            ->withSession(['url.intended' => route('crm.users.index')])
            ->patch(route('crm.onboarding.work.update'), [
                'department_id' => $department->id,
                'position_id' => $position->id,
                'reports_to_user_id' => $manager->id,
                'employment_status' => 'active',
                'date_of_appointment' => '2026-04-22',
                'personal_payroll_number' => 'PR-009',
            ])->assertRedirect(route('crm.users.index'));

        $user->refresh();

        $this->assertFalse($user->requiresCrmOnboarding());
        $this->assertNotNull($user->crm_onboarded_at);
        $this->assertNull($user->crm_onboarding_step);
        $this->assertSame($department->id, $user->department_id);
        $this->assertSame($position->id, $user->position_id);
        $this->assertSame($manager->id, $user->reports_to_user_id);

        $this->actingAs($user)
            ->get(route('crm.dashboard'))
            ->assertOk();
    }

    public function test_profile_skip_is_available_only_when_identity_details_are_already_complete(): void
    {
        $completeUser = $this->createPendingOnboardingUser(attributes: [
            'email' => 'skip-profile@example.com',
            'name' => 'Skip Profile User',
            'phone' => '71000099',
            'id_number' => 'ID-9000',
            'date_of_birth' => '1991-03-10',
            'gender' => 'male',
            'nationality' => 'Motswana',
        ]);

        $this->actingAs($completeUser)
            ->get(route('crm.onboarding.profile'))
            ->assertOk()
            ->assertSee('action="'.route('crm.onboarding.profile.skip').'"', false);

        $this->actingAs($completeUser)
            ->post(route('crm.onboarding.profile.skip'))
            ->assertRedirect(route('crm.onboarding.work'));

        $completeUser->refresh();

        $this->assertSame(2, $completeUser->crm_onboarding_step);

        $incompleteUser = $this->createPendingOnboardingUser(attributes: [
            'email' => 'skip-profile-incomplete@example.com',
        ]);

        $this->actingAs($incompleteUser)
            ->get(route('crm.onboarding.profile'))
            ->assertOk()
            ->assertDontSee('action="'.route('crm.onboarding.profile.skip').'"', false);

        $this->actingAs($incompleteUser)
            ->post(route('crm.onboarding.profile.skip'))
            ->assertForbidden();
    }

    public function test_work_skip_is_available_only_when_work_details_are_already_complete(): void
    {
        $manager = $this->createUser([
            'email' => 'skip-manager@example.com',
            'name' => 'Skip Manager',
        ]);

        $department = CrmUserDepartment::query()->create([
            'name' => 'Operations',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $position = CrmUserPosition::query()->create([
            'name' => 'Coordinator',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $completeUser = $this->createPendingOnboardingUser(step: 2, attributes: [
            'email' => 'skip-work@example.com',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'reports_to_user_id' => $manager->id,
            'employment_status' => 'active',
            'date_of_appointment' => '2026-04-22',
        ]);

        $this->actingAs($completeUser)
            ->get(route('crm.onboarding.work'))
            ->assertOk()
            ->assertSee('action="'.route('crm.onboarding.work.skip').'"', false);

        $this->actingAs($completeUser)
            ->withSession(['url.intended' => route('crm.users.index')])
            ->post(route('crm.onboarding.work.skip'))
            ->assertRedirect(route('crm.users.index'));

        $completeUser->refresh();

        $this->assertFalse($completeUser->requiresCrmOnboarding());
        $this->assertNotNull($completeUser->crm_onboarded_at);

        $incompleteUser = $this->createPendingOnboardingUser(step: 2, attributes: [
            'email' => 'skip-work-incomplete@example.com',
        ]);

        $this->actingAs($incompleteUser)
            ->get(route('crm.onboarding.work'))
            ->assertOk()
            ->assertDontSee('action="'.route('crm.onboarding.work.skip').'"', false);

        $this->actingAs($incompleteUser)
            ->post(route('crm.onboarding.work.skip'))
            ->assertForbidden();
    }

    public function test_existing_onboarded_users_still_land_on_their_intended_crm_route_after_login(): void
    {
        $user = $this->createUser([
            'email' => 'complete-user@example.com',
            'crm_onboarded_at' => now(),
        ]);

        $this->withSession(['url.intended' => route('crm.users.index')])
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'password123',
            ])->assertRedirect(route('crm.users.index'));
    }

    public function test_onboarding_migration_backfills_lookup_tables_from_legacy_user_fields(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('reporting_to')->nullable();
        });

        $manager = $this->createUser([
            'email' => 'legacy-manager@example.com',
        ]);

        $user = $this->createUser([
            'email' => 'legacy-user@example.com',
        ]);

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'department' => 'Administration',
                'position' => 'Teacher',
                'reporting_to' => (string) $manager->id,
                'department_id' => null,
                'position_id' => null,
                'reports_to_user_id' => null,
            ]);

        $migration = require base_path('database/migrations/2026_04_22_000000_add_crm_onboarding_state_to_users.php');
        $migration->up();

        $user->refresh();

        $this->assertDatabaseHas('crm_user_departments', [
            'name' => 'Administration',
        ]);

        $this->assertDatabaseHas('crm_user_positions', [
            'name' => 'Teacher',
        ]);

        $this->assertNotNull($user->department_id);
        $this->assertNotNull($user->position_id);
        $this->assertSame($manager->id, $user->reports_to_user_id);
    }

    private function createPendingOnboardingUser(int $step = 1, array $attributes = []): User
    {
        return $this->createUser(array_merge([
            'crm_onboarding_required_at' => now(),
            'crm_onboarding_step' => $step,
            'crm_onboarded_at' => null,
        ], $attributes));
    }

    private function createUser(array $attributes = []): User
    {
        $user = User::query()->create(array_merge([
            'name' => 'CRM User',
            'email' => 'user-' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'active' => true,
        ], $attributes));

        app(CrmModulePermissionService::class)->syncDefaultsForRole($user);

        return $user->fresh();
    }
}

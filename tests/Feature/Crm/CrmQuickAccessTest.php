<?php

namespace Tests\Feature\Crm;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmQuickAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_sign_in_button_route_creates_and_logs_in_an_admin_in_debug_mode(): void
    {
        config(['app.debug' => true]);

        $this->get(route('website.sign-in'))
            ->assertRedirect(route('crm.dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Heritage CRM Admin',
            'role' => 'admin',
            'active' => true,
        ]);
    }

    public function test_sign_in_button_route_falls_back_to_standard_login_when_quick_access_is_disabled(): void
    {
        config(['app.debug' => false]);

        $this->get(route('website.sign-in'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertSame(0, User::query()->count());
    }
}

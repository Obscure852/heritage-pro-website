<?php

namespace Tests\Feature\Crm;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmQuickAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_sign_in_button_route_redirects_to_login_even_in_debug_mode(): void
    {
        config(['app.debug' => true]);

        $this->get(route('website.sign-in'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertSame(0, User::query()->count());
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

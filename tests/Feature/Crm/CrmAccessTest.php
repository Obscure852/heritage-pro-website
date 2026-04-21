<?php

namespace Tests\Feature\Crm;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_users_are_redirected_from_the_crm_dashboard(): void
    {
        $this->get(route('crm.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_internal_users_land_on_crm_after_login(): void
    {
        $user = $this->createUser([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'active' => true,
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect('/crm');
    }

    public function test_sales_reps_only_see_their_owned_leads(): void
    {
        $rep = $this->createUser([
            'email' => 'rep@example.com',
            'role' => 'rep',
        ]);

        $otherRep = $this->createUser([
            'email' => 'other-rep@example.com',
            'role' => 'rep',
        ]);

        Lead::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'Heritage South',
            'status' => 'active',
        ]);

        Lead::query()->create([
            'owner_id' => $otherRep->id,
            'company_name' => 'Heritage North',
            'status' => 'active',
        ]);

        $this->actingAs($rep)
            ->get(route('crm.leads.index'))
            ->assertOk()
            ->assertSee('Heritage South')
            ->assertDontSee('Heritage North');
    }

    public function test_sales_rep_can_hard_delete_their_owned_lead(): void
    {
        $rep = $this->createUser([
            'email' => 'rep-delete@example.com',
            'role' => 'rep',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'Delete Me Lead',
            'status' => 'active',
        ]);

        $this->actingAs($rep)
            ->delete(route('crm.leads.destroy', $lead))
            ->assertRedirect(route('crm.leads.index'));

        $this->assertDatabaseMissing('leads', [
            'id' => $lead->id,
        ]);
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
}

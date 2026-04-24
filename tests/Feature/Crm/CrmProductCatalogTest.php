<?php

namespace Tests\Feature\Crm;

use App\Models\CrmProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_user_can_create_update_and_deactivate_catalog_items(): void
    {
        $finance = $this->createUser([
            'email' => 'finance-catalog@example.com',
            'role' => 'finance',
        ]);

        $this->actingAs($finance)
            ->post(route('crm.products.catalog.store'), [
                'code' => 'pro-suite',
                'name' => 'Pro Suite',
                'type' => 'license',
                'billing_frequency' => 'quarterly',
                'default_unit_label' => 'license',
                'default_unit_price' => '2500.00',
                'cpi_increase_rate' => '5.00',
                'default_tax_rate' => '14.00',
                'active' => '1',
                'description' => 'Annual CRM platform license.',
            ])
            ->assertRedirect();

        $product = CrmProduct::query()->firstOrFail();

        $this->assertDatabaseHas('crm_products', [
            'id' => $product->id,
            'code' => 'PRO-SUITE',
            'name' => 'Pro Suite',
            'billing_frequency' => 'quarterly',
            'cpi_increase_rate' => '5.00',
            'active' => true,
        ]);

        $this->actingAs($finance)
            ->patch(route('crm.products.catalog.update', $product), [
                'code' => 'PRO-SUITE',
                'name' => 'Pro Suite Enterprise',
                'type' => 'license',
                'billing_frequency' => 'quarterly',
                'default_unit_label' => 'license',
                'default_unit_price' => '3500.00',
                'cpi_increase_rate' => '7.50',
                'default_tax_rate' => '15.00',
                'active' => '1',
                'description' => 'Updated annual CRM platform license.',
                'notes' => 'Applies to enterprise deals.',
            ])
            ->assertRedirect(route('crm.products.catalog.edit', $product));

        $this->assertDatabaseHas('crm_products', [
            'id' => $product->id,
            'name' => 'Pro Suite Enterprise',
            'default_unit_price' => '3500.00',
            'cpi_increase_rate' => '7.50',
            'default_tax_rate' => '15.00',
        ]);

        $this->actingAs($finance)
            ->get(route('crm.products.catalog.show', $product))
            ->assertOk()
            ->assertSee('Quarterly')
            ->assertSee('CPI adjusted price')
            ->assertSee('3,762.50');

        $this->actingAs($finance)
            ->patch(route('crm.products.catalog.status', $product), [
                'active' => '0',
            ])
            ->assertRedirect(route('crm.products.catalog.show', $product));

        $this->assertDatabaseHas('crm_products', [
            'id' => $product->id,
            'active' => false,
        ]);
    }

    public function test_manager_can_browse_catalog_but_cannot_manage_it(): void
    {
        $manager = $this->createUser([
            'email' => 'manager-catalog@example.com',
            'role' => 'manager',
        ]);

        $product = CrmProduct::query()->create([
            'code' => 'SUPPORT-01',
            'name' => 'Premium Support',
            'type' => 'support',
            'billing_frequency' => 'annual',
            'default_unit_label' => 'package',
            'default_unit_price' => 1200,
            'default_tax_rate' => 0,
            'active' => true,
        ]);

        $this->actingAs($manager)
            ->get(route('crm.products.catalog.index'))
            ->assertOk()
            ->assertSee('Premium Support')
            ->assertDontSee('New product');

        $this->actingAs($manager)
            ->get(route('crm.products.catalog.show', $product))
            ->assertOk()
            ->assertSee('Premium Support');

        $this->actingAs($manager)
            ->get(route('crm.products.catalog.create'))
            ->assertForbidden();

        $this->actingAs($manager)
            ->post(route('crm.products.catalog.store'), [
                'name' => 'Blocked Item',
                'type' => 'service',
                'billing_frequency' => 'one_time',
                'default_unit_label' => 'unit',
                'default_unit_price' => '10.00',
            ])
            ->assertForbidden();

        $this->actingAs($manager)
            ->get(route('crm.products.catalog.edit', $product))
            ->assertForbidden();

        $this->actingAs($manager)
            ->patch(route('crm.products.catalog.status', $product), [
                'active' => '0',
            ])
            ->assertForbidden();
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

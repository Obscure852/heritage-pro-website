<?php

namespace Tests\Feature\Crm;

use App\Models\CrmCommercialCurrency;
use App\Models\CrmProductUnit;
use App\Models\CrmSector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmCommercialSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_user_can_update_commercial_settings_and_manage_currencies(): void
    {
        $finance = $this->createUser([
            'email' => 'finance-settings@example.com',
            'role' => 'finance',
        ]);

        $this->actingAs($finance)
            ->get(route('crm.products.settings'))
            ->assertOk()
            ->assertSee('Save product settings')
            ->assertSee('Document defaults')
            ->assertSee('Currencies')
            ->assertSee('Units')
            ->assertSee('Sectors')
            ->assertSee('data-bs-target="#documentDefaultsModal"', false)
            ->assertDontSee('data-bs-target="#currencyModal"', false);

        $this->actingAs($finance)
            ->get(route('crm.products.settings', ['tab' => 'currencies']))
            ->assertOk()
            ->assertSee('Configured currencies')
            ->assertSee('data-bs-target="#currencyModal"', false)
            ->assertSee('<table class="crm-table">', false);

        $this->actingAs($finance)
            ->get(route('crm.settings.commercial'))
            ->assertRedirect(route('crm.products.settings'));

        $this->actingAs($finance)
            ->post(route('crm.products.settings.currencies.store'), [
                'code' => 'usd',
                'name' => 'United States Dollar',
                'symbol' => '$',
                'symbol_position' => 'before',
                'precision' => 2,
                'is_active' => '1',
            ])
            ->assertRedirect(route('crm.products.settings', ['tab' => 'currencies']));

        $usd = CrmCommercialCurrency::query()->where('code', 'USD')->firstOrFail();

        $this->assertDatabaseHas('crm_commercial_currencies', [
            'id' => $usd->id,
            'code' => 'USD',
            'is_active' => true,
        ]);

        $this->actingAs($finance)
            ->patch(route('crm.products.settings.update'), [
                'default_currency_id' => $usd->id,
                'quote_prefix' => 'QTE',
                'quote_next_sequence' => 12,
                'invoice_prefix' => 'BILL',
                'invoice_next_sequence' => 27,
                'default_tax_rate' => '16.50',
                'allow_document_discounts' => '1',
            ])
            ->assertRedirect(route('crm.products.settings'));

        $this->assertDatabaseHas('crm_commercial_settings', [
            'default_currency_id' => $usd->id,
            'quote_prefix' => 'QTE',
            'quote_next_sequence' => 12,
            'invoice_prefix' => 'BILL',
            'invoice_next_sequence' => 27,
            'default_tax_rate' => '16.50',
            'allow_line_discounts' => false,
            'allow_document_discounts' => true,
        ]);

        $this->actingAs($finance)
            ->patch(route('crm.products.settings.currencies.update', $usd), [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'symbol_position' => 'before',
                'precision' => 2,
                'is_active' => '1',
            ])
            ->assertRedirect(route('crm.products.settings.edit-currency', $usd));

        $this->assertDatabaseHas('crm_commercial_currencies', [
            'id' => $usd->id,
            'name' => 'US Dollar',
        ]);

        $bwp = CrmCommercialCurrency::query()->where('code', 'BWP')->firstOrFail();

        $this->actingAs($finance)
            ->delete(route('crm.products.settings.currencies.destroy', $bwp))
            ->assertRedirect(route('crm.products.settings', ['tab' => 'currencies']));

        $this->assertDatabaseHas('crm_commercial_currencies', [
            'id' => $bwp->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('crm_product_units', [
            'label' => 'license',
            'is_active' => true,
        ]);

        $this->actingAs($finance)
            ->get(route('crm.products.settings', ['tab' => 'units']))
            ->assertOk()
            ->assertSee('Configured units')
            ->assertSee('License')
            ->assertSee('data-bs-target="#unitModal"', false)
            ->assertSee('<table class="crm-table">', false);

        $this->actingAs($finance)
            ->post(route('crm.products.settings.units.store'), [
                'name' => 'Term',
                'label' => 'term',
                'sort_order' => 115,
                'is_active' => '1',
            ])
            ->assertRedirect(route('crm.products.settings', ['tab' => 'units']));

        $term = CrmProductUnit::query()->where('label', 'term')->firstOrFail();

        $this->assertDatabaseHas('crm_product_units', [
            'id' => $term->id,
            'name' => 'Term',
            'label' => 'term',
            'is_active' => true,
        ]);

        $this->actingAs($finance)
            ->delete(route('crm.products.settings.units.destroy', $term))
            ->assertRedirect(route('crm.products.settings', ['tab' => 'units']));

        $this->assertDatabaseHas('crm_product_units', [
            'id' => $term->id,
            'is_active' => false,
        ]);

        $this->actingAs($finance)
            ->patch(route('crm.products.settings.units.update', $term), [
                'name' => 'Term block',
                'label' => 'term',
                'sort_order' => 116,
            ])
            ->assertRedirect(route('crm.products.settings.edit-unit', $term));

        $this->assertDatabaseHas('crm_product_units', [
            'id' => $term->id,
            'name' => 'Term block',
            'sort_order' => 116,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('crm_sectors', [
            'name' => 'Education',
            'is_active' => true,
        ]);

        $this->actingAs($finance)
            ->get(route('crm.products.settings', ['tab' => 'sectors']))
            ->assertOk()
            ->assertSee('Configured sectors')
            ->assertSee('Education')
            ->assertSee('data-bs-target="#sectorModal"', false)
            ->assertSee('<table class="crm-table">', false);

        $this->actingAs($finance)
            ->post(route('crm.products.settings.sectors.store'), [
                'name' => 'Independent School',
                'sort_order' => 15,
                'is_active' => '1',
            ])
            ->assertRedirect(route('crm.products.settings', ['tab' => 'sectors']));

        $sector = CrmSector::query()->where('name', 'Independent School')->firstOrFail();

        $this->assertDatabaseHas('crm_sectors', [
            'id' => $sector->id,
            'name' => 'Independent School',
            'sort_order' => 15,
            'is_active' => true,
        ]);

        $this->actingAs($finance)
            ->delete(route('crm.products.settings.sectors.destroy', $sector))
            ->assertRedirect(route('crm.products.settings', ['tab' => 'sectors']));

        $this->assertDatabaseHas('crm_sectors', [
            'id' => $sector->id,
            'is_active' => false,
        ]);

        $this->actingAs($finance)
            ->patch(route('crm.products.settings.sectors.update', $sector), [
                'name' => 'Independent Schools',
                'sort_order' => 16,
            ])
            ->assertRedirect(route('crm.products.settings.edit-sector', $sector));

        $this->assertDatabaseHas('crm_sectors', [
            'id' => $sector->id,
            'name' => 'Independent Schools',
            'sort_order' => 16,
            'is_active' => false,
        ]);
    }

    public function test_default_currency_cannot_be_deactivated_without_reassignment(): void
    {
        $finance = $this->createUser([
            'email' => 'finance-default-currency@example.com',
            'role' => 'finance',
        ]);

        $defaultCurrency = CrmCommercialCurrency::query()->where('code', 'BWP')->firstOrFail();

        $this->actingAs($finance)
            ->patch(route('crm.products.settings.currencies.update', $defaultCurrency), [
                'code' => 'BWP',
                'name' => 'Botswana Pula',
                'symbol' => 'P',
                'symbol_position' => 'before',
                'precision' => 2,
                'is_active' => '0',
            ])
            ->assertRedirect(route('crm.products.settings.edit-currency', $defaultCurrency))
            ->assertSessionHasErrors('currency');

        $this->actingAs($finance)
            ->delete(route('crm.products.settings.currencies.destroy', $defaultCurrency))
            ->assertRedirect(route('crm.products.settings', ['tab' => 'currencies']))
            ->assertSessionHasErrors('currency');

        $this->assertDatabaseHas('crm_commercial_currencies', [
            'id' => $defaultCurrency->id,
            'is_active' => true,
        ]);
    }

    public function test_manager_cannot_access_commercial_settings(): void
    {
        $manager = $this->createUser([
            'email' => 'manager-commercial-settings@example.com',
            'role' => 'manager',
        ]);
        $currency = CrmCommercialCurrency::query()->where('code', 'BWP')->firstOrFail();
        $unit = CrmProductUnit::query()->firstOrFail();
        $sector = CrmSector::query()->firstOrFail();

        $this->actingAs($manager)
            ->get(route('crm.products.settings'))
            ->assertForbidden();

        $this->actingAs($manager)
            ->patch(route('crm.products.settings.update'), [
                'default_currency_id' => CrmCommercialCurrency::query()->where('code', 'BWP')->value('id'),
                'quote_prefix' => 'QT',
                'quote_next_sequence' => 1,
                'invoice_prefix' => 'INV',
                'invoice_next_sequence' => 1,
            ])
            ->assertForbidden();

        $this->actingAs($manager)
            ->post(route('crm.products.settings.currencies.store'), [
                'code' => 'USD',
                'name' => 'United States Dollar',
                'symbol' => '$',
                'symbol_position' => 'before',
                'precision' => 2,
            ])
            ->assertForbidden();

        $this->actingAs($manager)
            ->delete(route('crm.products.settings.currencies.destroy', $currency))
            ->assertForbidden();

        $this->actingAs($manager)
            ->post(route('crm.products.settings.units.store'), [
                'name' => 'Term',
                'label' => 'term',
            ])
            ->assertForbidden();

        $this->actingAs($manager)
            ->delete(route('crm.products.settings.units.destroy', $unit))
            ->assertForbidden();

        $this->actingAs($manager)
            ->post(route('crm.products.settings.sectors.store'), [
                'name' => 'Independent School',
            ])
            ->assertForbidden();

        $this->actingAs($manager)
            ->delete(route('crm.products.settings.sectors.destroy', $sector))
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

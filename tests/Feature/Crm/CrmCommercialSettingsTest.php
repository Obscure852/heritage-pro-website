<?php

namespace Tests\Feature\Crm;

use App\Models\CrmCommercialCurrency;
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
            ->get(route('crm.settings.commercial'))
            ->assertOk()
            ->assertSee('Save commercial settings');

        $this->actingAs($finance)
            ->post(route('crm.settings.commercial.currencies.store'), [
                'code' => 'usd',
                'name' => 'United States Dollar',
                'symbol' => '$',
                'symbol_position' => 'before',
                'precision' => 2,
                'is_active' => '1',
            ])
            ->assertRedirect(route('crm.settings.commercial'));

        $usd = CrmCommercialCurrency::query()->where('code', 'USD')->firstOrFail();

        $this->assertDatabaseHas('crm_commercial_currencies', [
            'id' => $usd->id,
            'code' => 'USD',
            'is_active' => true,
        ]);

        $this->actingAs($finance)
            ->patch(route('crm.settings.commercial.update'), [
                'default_currency_id' => $usd->id,
                'quote_prefix' => 'QTE',
                'quote_next_sequence' => 12,
                'invoice_prefix' => 'BILL',
                'invoice_next_sequence' => 27,
                'default_tax_rate' => '16.50',
                'allow_document_discounts' => '1',
            ])
            ->assertRedirect(route('crm.settings.commercial'));

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
            ->patch(route('crm.settings.commercial.currencies.update', $usd), [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'symbol_position' => 'before',
                'precision' => 2,
                'is_active' => '1',
            ])
            ->assertRedirect(route('crm.settings.commercial.edit-currency', $usd));

        $this->assertDatabaseHas('crm_commercial_currencies', [
            'id' => $usd->id,
            'name' => 'US Dollar',
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
            ->patch(route('crm.settings.commercial.currencies.update', $defaultCurrency), [
                'code' => 'BWP',
                'name' => 'Botswana Pula',
                'symbol' => 'P',
                'symbol_position' => 'before',
                'precision' => 2,
                'is_active' => '0',
            ])
            ->assertRedirect(route('crm.settings.commercial.edit-currency', $defaultCurrency))
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

        $this->actingAs($manager)
            ->get(route('crm.settings.commercial'))
            ->assertForbidden();

        $this->actingAs($manager)
            ->patch(route('crm.settings.commercial.update'), [
                'default_currency_id' => CrmCommercialCurrency::query()->where('code', 'BWP')->value('id'),
                'quote_prefix' => 'QT',
                'quote_next_sequence' => 1,
                'invoice_prefix' => 'INV',
                'invoice_next_sequence' => 1,
            ])
            ->assertForbidden();

        $this->actingAs($manager)
            ->post(route('crm.settings.commercial.currencies.store'), [
                'code' => 'USD',
                'name' => 'United States Dollar',
                'symbol' => '$',
                'symbol_position' => 'before',
                'precision' => 2,
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

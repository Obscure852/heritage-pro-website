<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('crm_commercial_settings')) {
            return;
        }

        Schema::create('crm_commercial_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('default_currency_id')
                ->nullable()
                ->constrained('crm_commercial_currencies')
                ->nullOnDelete();
            $table->string('company_name', 160)->nullable();
            $table->string('company_email', 160)->nullable();
            $table->string('company_phone', 40)->nullable();
            $table->string('company_website', 160)->nullable();
            $table->string('company_address_line_1', 160)->nullable();
            $table->string('company_address_line_2', 160)->nullable();
            $table->string('company_city', 120)->nullable();
            $table->string('company_state', 120)->nullable();
            $table->string('company_country', 120)->nullable();
            $table->string('company_postal_code', 40)->nullable();
            $table->string('quote_prefix', 20)->default('QT');
            $table->unsignedInteger('quote_next_sequence')->default(1);
            $table->string('invoice_prefix', 20)->default('INV');
            $table->unsignedInteger('invoice_next_sequence')->default(1);
            $table->decimal('default_tax_rate', 5, 2)->default(0);
            $table->boolean('allow_line_discounts')->default(true);
            $table->boolean('allow_document_discounts')->default(true);
            $table->string('company_logo_path')->nullable();
            $table->string('login_image_path')->nullable();
            $table->timestamps();
        });

        $defaultCurrencyId = DB::table('crm_commercial_currencies')
            ->where('code', 'BWP')
            ->value('id');

        DB::table('crm_commercial_settings')->insert([
            'default_currency_id' => $defaultCurrencyId,
            'company_name' => 'Heritage Pro',
            'quote_prefix' => 'QT',
            'quote_next_sequence' => 1,
            'invoice_prefix' => 'INV',
            'invoice_next_sequence' => 1,
            'default_tax_rate' => 0,
            'allow_line_discounts' => true,
            'allow_document_discounts' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_commercial_settings');
    }
};

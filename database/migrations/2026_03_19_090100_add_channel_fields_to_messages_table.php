<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('channel')->default('sms')->after('body');
            $table->string('provider')->nullable()->after('channel');
            $table->string('recipient_address')->nullable()->after('provider');
            $table->string('template_name')->nullable()->after('recipient_address');
            $table->string('template_external_id')->nullable()->after('template_name');
            $table->json('metadata')->nullable()->after('template_external_id');

            $table->index('channel');
            $table->index('provider');
            $table->index('template_external_id');
        });

        DB::table('messages')->whereNull('channel')->update([
            'channel' => 'sms',
            'provider' => 'link_sms',
        ]);
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['channel']);
            $table->dropIndex(['provider']);
            $table->dropIndex(['template_external_id']);

            $table->dropColumn([
                'channel',
                'provider',
                'recipient_address',
                'template_name',
                'template_external_id',
                'metadata',
            ]);
        });
    }
};

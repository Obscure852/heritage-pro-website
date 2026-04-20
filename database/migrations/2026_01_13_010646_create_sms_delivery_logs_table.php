<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->index();
            $table->string('external_id')->nullable()->index();
            $table->string('phone_number');
            $table->string('status');
            $table->string('status_code')->nullable();
            $table->text('status_message')->nullable();
            $table->string('provider')->default('link_sms');
            $table->json('raw_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['phone_number', 'status']);
            $table->index('created_at');
        });

        // Add delivery tracking columns to messages table
        Schema::table('messages', function (Blueprint $table) {
            $table->string('external_message_id')->nullable()->after('status');
            $table->string('delivery_status')->default('pending')->after('external_message_id');
            $table->timestamp('delivered_at')->nullable()->after('delivery_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms_delivery_logs');

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['external_message_id', 'delivery_status', 'delivered_at']);
        });
    }
};

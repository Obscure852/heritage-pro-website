<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('crm_calendars')) {
            Schema::create('crm_calendars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->string('type', 20)->default('personal');
                $table->string('color', 20)->default('#5156be');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->json('settings')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['owner_id', 'type']);
                $table->index(['type', 'is_active']);
            });
        }

        if (! Schema::hasTable('crm_calendar_memberships')) {
            Schema::create('crm_calendar_memberships', function (Blueprint $table) {
                $table->id();
                $table->foreignId('calendar_id')->constrained('crm_calendars')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('permission', 20)->default('view');
                $table->boolean('is_visible')->default(true);
                $table->timestamps();

                $table->unique(['calendar_id', 'user_id']);
                $table->index(['user_id', 'permission']);
            });
        }

        if (! Schema::hasTable('crm_calendar_events')) {
            Schema::create('crm_calendar_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('calendar_id')->constrained('crm_calendars')->cascadeOnDelete();
                $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
                $table->foreignId('request_id')->nullable()->constrained('requests')->nullOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('location')->nullable();
                $table->timestamp('starts_at');
                $table->timestamp('ends_at');
                $table->boolean('all_day')->default(false);
                $table->string('status', 20)->default('scheduled');
                $table->string('visibility', 20)->default('standard');
                $table->string('timezone', 80)->nullable();
                $table->json('reminders')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['calendar_id', 'starts_at']);
                $table->index(['owner_id', 'starts_at']);
                $table->index(['request_id', 'starts_at']);
                $table->index(['lead_id', 'customer_id']);
            });
        }

        if (! Schema::hasTable('crm_calendar_event_attendees')) {
            Schema::create('crm_calendar_event_attendees', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->constrained('crm_calendar_events')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
                $table->string('display_name')->nullable();
                $table->string('email')->nullable();
                $table->string('role', 20)->default('required');
                $table->string('response_status', 20)->default('pending');
                $table->timestamps();

                $table->index(['event_id', 'role']);
                $table->index(['user_id', 'contact_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_calendar_event_attendees');
        Schema::dropIfExists('crm_calendar_events');
        Schema::dropIfExists('crm_calendar_memberships');
        Schema::dropIfExists('crm_calendars');
    }
};

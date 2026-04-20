<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('activity_events')) {
            Schema::create('activity_events', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->string('title');
                $table->string('event_type', 50);
                $table->text('description')->nullable();
                $table->dateTime('start_datetime');
                $table->dateTime('end_datetime')->nullable();
                $table->string('location')->nullable();
                $table->string('opponent_or_partner_name')->nullable();
                $table->boolean('house_linked')->default(false);
                $table->boolean('publish_to_calendar')->default(false);
                $table->string('calendar_sync_status', 30)->default('not_published');
                $table->string('status', 20)->default('scheduled');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('activity_id')->references('id')->on('activities')->cascadeOnDelete();
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

                $table->index(['activity_id', 'status']);
                $table->index('start_datetime');
            });
        }

        if (!Schema::hasTable('activity_results')) {
            Schema::create('activity_results', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('activity_event_id');
                $table->string('participant_type', 30);
                $table->unsignedBigInteger('participant_id');
                $table->string('metric_type', 30);
                $table->decimal('score_value', 10, 2)->nullable();
                $table->unsignedInteger('placement')->nullable();
                $table->integer('points')->nullable();
                $table->string('award_name')->nullable();
                $table->string('result_label')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('recorded_by')->nullable();
                $table->timestamps();

                $table->foreign('activity_event_id')->references('id')->on('activity_events')->cascadeOnDelete();
                $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();

                $table->index(['activity_event_id', 'participant_type', 'participant_id'], 'activity_results_participant_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_results');
        Schema::dropIfExists('activity_events');
    }
};

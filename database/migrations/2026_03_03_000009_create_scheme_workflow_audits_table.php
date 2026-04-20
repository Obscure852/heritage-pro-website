<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('scheme_workflow_audits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('scheme_of_work_id');
            $table->unsignedBigInteger('actor_id');
            $table->string('action', 50);
            $table->string('from_status', 30);
            $table->string('to_status', 30);
            $table->text('comments')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('scheme_of_work_id')
                  ->references('id')->on('schemes_of_work')
                  ->onDelete('cascade');

            $table->foreign('actor_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->index('scheme_of_work_id');
            $table->index('actor_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void {
        Schema::dropIfExists('scheme_workflow_audits');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('crm_commercial_document_artifacts')) {
            return;
        }

        Schema::create('crm_commercial_document_artifacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable();
            $table->foreignId('quote_id')->nullable();
            $table->foreignId('invoice_id')->nullable();
            $table->foreignId('generated_by_id')->nullable();
            $table->foreignId('shared_discussion_thread_id')->nullable();
            $table->string('disk', 40)->default('documents');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 150)->default('application/pdf');
            $table->string('extension', 20)->default('pdf');
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamp('source_updated_at')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique('quote_id');
            $table->unique('invoice_id');
            $table->index(['owner_id', 'generated_at']);

            $table->foreign('owner_id', 'crm_com_doc_art_owner_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->foreign('quote_id', 'crm_com_doc_art_quote_fk')
                ->references('id')
                ->on('crm_quotes')
                ->cascadeOnDelete();
            $table->foreign('invoice_id', 'crm_com_doc_art_invoice_fk')
                ->references('id')
                ->on('crm_invoices')
                ->cascadeOnDelete();
            $table->foreign('generated_by_id', 'crm_com_doc_art_gen_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->foreign('shared_discussion_thread_id', 'crm_com_doc_art_thread_fk')
                ->references('id')
                ->on('crm_discussion_threads')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_commercial_document_artifacts');
    }
};

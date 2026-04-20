<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the document_folder_permissions table for granular folder access control.
 * Uses polymorphic permissionable_type/permissionable_id to grant permissions
 * to users, roles, or departments on specific folders.
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasTable('document_folder_permissions')) {
            return;
        }

        Schema::create('document_folder_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_id');

            // Polymorphic permission target (user, role, department)
            $table->string('permissionable_type', 50);
            $table->unsignedBigInteger('permissionable_id');

            // Permission level (string, not enum)
            $table->string('permission_level', 20)->default('view'); // view, upload, edit, manage

            $table->unsignedBigInteger('granted_by_user_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('folder_id')
                ->references('id')
                ->on('document_folders')
                ->onDelete('cascade');

            $table->foreign('granted_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Unique constraint: one permission per folder + target combination
            $table->unique(
                ['folder_id', 'permissionable_type', 'permissionable_id'],
                'uk_folder_permission'
            );

            // Index for reverse lookups (find all folders a user/role/department has access to)
            $table->index(
                ['permissionable_type', 'permissionable_id'],
                'idx_folder_perms_permissionable'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('document_folder_permissions');
    }
};

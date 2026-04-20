<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Seed document management role and permission definitions.
     *
     * This project uses custom roles (roles table) + Gate definitions
     * for authorization rather than Spatie Permission. This migration:
     *
     * 1. Creates the 'Documents Admin' role needed by document management gates
     * 2. Stores 14 permission definitions in a reference table for admin UIs
     *
     * The actual permission enforcement is handled by Gate definitions in
     * AuthServiceProvider.php (PRM-01 through PRM-12).
     */
    public function up(): void {
        // Seed the 'Documents Admin' role required by document management gates
        Role::firstOrCreate(
            ['name' => 'Documents Admin'],
            ['description' => 'Full administrative access to the Document Management System']
        );

        // Create a reference table for document permission definitions
        // Used by admin UIs to display available DMS permissions
        if (!\Illuminate\Support\Facades\Schema::hasTable('document_permissions')) {
            \Illuminate\Support\Facades\Schema::create('document_permissions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('name')->unique()->comment('Permission identifier (e.g., documents.view)');
                $table->string('gate_name')->comment('Corresponding Gate name in AuthServiceProvider');
                $table->string('description');
                $table->string('group')->default('general')->comment('Permission group for UI organization');
                $table->timestamps();
            });
        }

        $permissions = [
            ['name' => 'documents.view', 'gate_name' => 'access-documents', 'description' => 'View document listings and dashboard', 'group' => 'access'],
            ['name' => 'documents.create', 'gate_name' => 'create-documents', 'description' => 'Upload and create new documents', 'group' => 'crud'],
            ['name' => 'documents.edit', 'gate_name' => 'manage-own-documents', 'description' => 'Edit own documents', 'group' => 'crud'],
            ['name' => 'documents.edit_any', 'gate_name' => 'edit-any-documents', 'description' => 'Edit any document regardless of ownership', 'group' => 'admin'],
            ['name' => 'documents.delete', 'gate_name' => 'manage-own-documents', 'description' => 'Delete own documents', 'group' => 'crud'],
            ['name' => 'documents.delete_any', 'gate_name' => 'delete-any-documents', 'description' => 'Delete any document regardless of ownership', 'group' => 'admin'],
            ['name' => 'documents.share', 'gate_name' => 'share-documents', 'description' => 'Share documents with other users', 'group' => 'sharing'],
            ['name' => 'documents.approve', 'gate_name' => 'approve-documents', 'description' => 'Review and approve documents', 'group' => 'workflow'],
            ['name' => 'documents.publish', 'gate_name' => 'publish-documents', 'description' => 'Publish approved documents', 'group' => 'workflow'],
            ['name' => 'documents.manage_categories', 'gate_name' => 'manage-document-categories', 'description' => 'Manage document categories and tags', 'group' => 'admin'],
            ['name' => 'documents.manage_folders', 'gate_name' => 'manage-institutional-folders', 'description' => 'Manage institutional folders', 'group' => 'admin'],
            ['name' => 'documents.view_audit', 'gate_name' => 'view-document-audit', 'description' => 'View document audit logs', 'group' => 'audit'],
            ['name' => 'documents.manage_settings', 'gate_name' => 'manage-document-settings', 'description' => 'Configure DMS module settings', 'group' => 'admin'],
            ['name' => 'documents.manage_quotas', 'gate_name' => 'manage-document-quotas', 'description' => 'Manage user storage quotas', 'group' => 'admin'],
        ];

        foreach ($permissions as $permission) {
            \Illuminate\Support\Facades\DB::table('document_permissions')->updateOrInsert(
                ['name' => $permission['name']],
                $permission
            );
        }
    }

    public function down(): void {
        \Illuminate\Support\Facades\Schema::dropIfExists('document_permissions');

        // Only remove the role if no users are assigned to it
        $role = Role::where('name', 'Documents Admin')->first();
        if ($role && $role->users()->count() === 0) {
            $role->delete();
        }
    }
};

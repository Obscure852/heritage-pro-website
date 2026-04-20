<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        $supportsFullText = Schema::getConnection()->getDriverName() !== 'sqlite';

        // Content library collections (folders/categories)
        Schema::create('lms_library_collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->default('#6366f1');
            $table->string('icon')->default('folder');
            $table->foreignId('parent_id')->nullable()->constrained('lms_library_collections')->cascadeOnDelete();
            $table->string('visibility')->default('private'); // private, shared, public
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['created_by', 'visibility']);
        });

        // Collection sharing
        Schema::create('lms_library_collection_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('lms_library_collections')->cascadeOnDelete();
            $table->morphs('shareable'); // User, Role, or Department
            $table->string('permission')->default('view'); // view, edit, manage
            $table->timestamps();

            $table->unique(['collection_id', 'shareable_type', 'shareable_id'], 'lms_collection_share_unique');
        });

        // Library items (reusable content)
        Schema::create('lms_library_items', function (Blueprint $table) use ($supportsFullText) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // video, document, image, audio, scorm, h5p, quiz_template, assignment_template
            $table->string('mime_type')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('external_url')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->integer('duration_seconds')->nullable(); // For video/audio
            $table->json('metadata')->nullable(); // Type-specific metadata
            $table->foreignId('collection_id')->nullable()->constrained('lms_library_collections')->nullOnDelete();
            $table->string('visibility')->default('private'); // private, shared, public
            $table->boolean('is_template')->default(false);
            $table->integer('usage_count')->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'visibility']);
            $table->index(['created_by', 'type']);

            if ($supportsFullText) {
                $table->fullText(['title', 'description']);
            }
        });

        // Library item tags
        Schema::create('lms_library_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('color')->default('#6b7280');
            $table->timestamps();
        });

        // Library item tag pivot
        Schema::create('lms_library_item_tag', function (Blueprint $table) {
            $table->foreignId('item_id')->constrained('lms_library_items')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('lms_library_tags')->cascadeOnDelete();

            $table->primary(['item_id', 'tag_id']);
        });

        // Library item sharing
        Schema::create('lms_library_item_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('lms_library_items')->cascadeOnDelete();
            $table->morphs('shareable'); // User, Role, or Department
            $table->string('permission')->default('view'); // view, edit, manage
            $table->timestamps();

            $table->unique(['item_id', 'shareable_type', 'shareable_id'], 'lms_item_share_unique');
        });

        // Library item versions
        Schema::create('lms_library_item_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('lms_library_items')->cascadeOnDelete();
            $table->integer('version_number');
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->json('metadata')->nullable();
            $table->text('change_notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['item_id', 'version_number']);
        });

        // Usage tracking (where library items are used)
        Schema::create('lms_library_item_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('lms_library_items')->cascadeOnDelete();
            $table->morphs('usable'); // ContentItem, Course, Module, etc.
            $table->foreignId('used_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['item_id', 'usable_type', 'usable_id'], 'lms_item_usage_unique');
        });

        // Favorites
        Schema::create('lms_library_favorites', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('lms_library_items')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['user_id', 'item_id']);
        });

        // Recently viewed
        Schema::create('lms_library_recent_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('lms_library_items')->cascadeOnDelete();
            $table->timestamp('viewed_at');

            $table->index(['user_id', 'viewed_at']);
        });

        // Content templates (pre-built course structures)
        Schema::create('lms_course_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('category')->nullable();
            $table->json('structure')->nullable(); // Modules, content placeholders
            $table->json('settings')->nullable(); // Default course settings
            $table->boolean('is_public')->default(false);
            $table->integer('usage_count')->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('lms_course_templates');
        Schema::dropIfExists('lms_library_recent_views');
        Schema::dropIfExists('lms_library_favorites');
        Schema::dropIfExists('lms_library_item_usages');
        Schema::dropIfExists('lms_library_item_versions');
        Schema::dropIfExists('lms_library_item_shares');
        Schema::dropIfExists('lms_library_item_tag');
        Schema::dropIfExists('lms_library_tags');
        Schema::dropIfExists('lms_library_items');
        Schema::dropIfExists('lms_library_collection_shares');
        Schema::dropIfExists('lms_library_collections');
    }
};

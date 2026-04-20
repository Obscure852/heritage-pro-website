<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // LTI Tool configurations (external tools that can be launched)
        Schema::create('lms_lti_tools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('tool_url'); // Launch URL
            $table->string('login_url')->nullable(); // OIDC login initiation URL
            $table->string('redirect_urls')->nullable(); // Allowed redirect URLs (comma-separated)
            $table->string('client_id')->unique();
            $table->string('deployment_id')->nullable();
            $table->text('public_key')->nullable(); // Tool's public key (PEM format)
            $table->string('public_key_url')->nullable(); // JWKS URL
            $table->string('lti_version')->default('1.3'); // 1.1, 1.3
            $table->json('custom_parameters')->nullable();
            $table->json('claims')->nullable(); // Required/optional claims
            $table->boolean('is_active')->default(true);
            $table->boolean('send_name')->default(true);
            $table->boolean('send_email')->default(true);
            $table->string('icon_url')->nullable();
            $table->string('privacy_level')->default('public'); // public, name_only, anonymous
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // LTI tool placements (where tools appear)
        Schema::create('lms_lti_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained('lms_lti_tools')->cascadeOnDelete();
            $table->string('placement_type'); // course_navigation, assignment, editor_button, etc.
            $table->string('label')->nullable(); // Override tool name
            $table->string('icon_url')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->json('message_type')->nullable(); // LtiResourceLinkRequest, LtiDeepLinkingRequest
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // Course-level tool configurations
        Schema::create('lms_course_lti_tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->foreignId('tool_id')->constrained('lms_lti_tools')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->json('custom_parameters')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'tool_id']);
        });

        // LTI resource links (actual tool instances in content)
        Schema::create('lms_lti_resource_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained('lms_lti_tools')->cascadeOnDelete();
            $table->foreignId('content_id')->nullable()->constrained('lms_content_items')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('lms_courses')->cascadeOnDelete();
            $table->string('resource_link_id')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('launch_url')->nullable(); // Override tool URL
            $table->json('custom_parameters')->nullable();
            $table->timestamps();
        });

        // LTI launches (audit log)
        Schema::create('lms_lti_launches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained('lms_lti_tools')->cascadeOnDelete();
            $table->foreignId('resource_link_id')->nullable()->constrained('lms_lti_resource_links')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('lms_courses')->cascadeOnDelete();
            $table->string('message_type'); // LtiResourceLinkRequest, LtiDeepLinkingRequest
            $table->string('lti_version');
            $table->json('claims_sent')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('launched_at');
            $table->timestamps();

            $table->index(['tool_id', 'launched_at']);
            $table->index(['user_id', 'launched_at']);
        });

        // LTI Nonces (replay prevention)
        Schema::create('lms_lti_nonces', function (Blueprint $table) {
            $table->id();
            $table->string('nonce')->unique();
            $table->foreignId('tool_id')->constrained('lms_lti_tools')->cascadeOnDelete();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('expires_at');
        });

        // LTI Access Tokens (for tool-to-platform API calls)
        Schema::create('lms_lti_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained('lms_lti_tools')->cascadeOnDelete();
            $table->text('token');
            $table->json('scopes')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('expires_at');
        });

        // LTI Deep Link content returns
        Schema::create('lms_lti_deep_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained('lms_lti_tools')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->foreignId('module_id')->nullable()->constrained('lms_modules')->cascadeOnDelete();
            $table->string('content_type'); // ltiResourceLink, file, html, image, link
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->json('custom')->nullable();
            $table->json('line_item')->nullable(); // For gradeable items
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Assignment and Grade Services (AGS) - Line Items
        Schema::create('lms_lti_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained('lms_lti_tools')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->foreignId('resource_link_id')->nullable()->constrained('lms_lti_resource_links')->nullOnDelete();
            $table->string('label');
            $table->decimal('score_maximum', 10, 2)->default(100);
            $table->string('tag')->nullable();
            $table->string('resource_id')->nullable();
            $table->timestamp('start_date_time')->nullable();
            $table->timestamp('end_date_time')->nullable();
            $table->boolean('grades_released')->default(false);
            $table->timestamps();

            $table->index(['course_id', 'tool_id']);
        });

        // LTI Scores (from tools via AGS)
        Schema::create('lms_lti_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_item_id')->constrained('lms_lti_line_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('score_given', 10, 2)->nullable();
            $table->decimal('score_maximum', 10, 2)->nullable();
            $table->text('comment')->nullable();
            $table->string('activity_progress')->default('Initialized'); // Initialized, Started, InProgress, Submitted, Completed
            $table->string('grading_progress')->default('NotReady'); // FullyGraded, Pending, PendingManual, Failed, NotReady
            $table->timestamp('timestamp');
            $table->timestamps();

            $table->unique(['line_item_id', 'user_id']);
        });

        // Platform keys for LTI 1.3
        Schema::create('lms_lti_platform_keys', function (Blueprint $table) {
            $table->id();
            $table->string('kid')->unique(); // Key ID
            $table->text('private_key');
            $table->text('public_key');
            $table->string('alg')->default('RS256');
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('lms_lti_platform_keys');
        Schema::dropIfExists('lms_lti_scores');
        Schema::dropIfExists('lms_lti_line_items');
        Schema::dropIfExists('lms_lti_deep_links');
        Schema::dropIfExists('lms_lti_access_tokens');
        Schema::dropIfExists('lms_lti_nonces');
        Schema::dropIfExists('lms_lti_launches');
        Schema::dropIfExists('lms_lti_resource_links');
        Schema::dropIfExists('lms_course_lti_tools');
        Schema::dropIfExists('lms_lti_placements');
        Schema::dropIfExists('lms_lti_tools');
    }
};

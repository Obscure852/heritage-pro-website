<?php

use Illuminate\Database\Migrations\Migration;
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
        // Drop tables in order (child tables first to respect foreign keys)
        Schema::dropIfExists('teacher_student_messages');
        Schema::dropIfExists('resource_attachments');
        Schema::dropIfExists('student_resource_progress');
        Schema::dropIfExists('topic_resources');
        Schema::dropIfExists('subject_topic_comments');
        Schema::dropIfExists('subject_topic_materials');
        Schema::dropIfExists('student_subject_content');
        Schema::dropIfExists('subject_topics');
        Schema::dropIfExists('subject_contents');
        Schema::dropIfExists('syllabus_items');
        Schema::dropIfExists('syllabi');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Tables cannot be recreated - learning module has been removed
    }
};

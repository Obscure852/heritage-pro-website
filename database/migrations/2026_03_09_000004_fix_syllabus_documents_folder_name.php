<?php

use App\Support\SyllabusDocumentSync;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        SyllabusDocumentSync::sync();
    }

    public function down(): void
    {
        // No-op. This migration only reapplies the canonical syllabus document
        // sync so existing databases pick up the corrected shared folder name.
    }
};

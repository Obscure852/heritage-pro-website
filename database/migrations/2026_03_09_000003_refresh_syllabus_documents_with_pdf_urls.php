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
        // Best-effort no-op. The canonical syllabus document seeding remains owned
        // by the original migration; this adjustment only reapplies the latest URLs
        // and folder assignment for already-migrated databases.
    }
};

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
        SyllabusDocumentSync::clearSeededLinksAndDocuments();
    }
};

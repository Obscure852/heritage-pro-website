<?php

use App\Support\SyllabusSeedRegistry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subjects')) {
            return;
        }

        if (!Schema::hasColumn('subjects', 'syllabus_url')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->string('syllabus_url', 2048)->nullable()->after('department');
            });
        }

        foreach (SyllabusSeedRegistry::subjectUrls() as $subject) {
            DB::table('subjects')
                ->whereNull('deleted_at')
                ->where('level', $subject['level'])
                ->where(function ($query) use ($subject) {
                    $query->where('abbrev', $subject['abbrev'])
                        ->orWhere('name', $subject['name']);
                })
                ->update([
                    'syllabus_url' => $subject['url'],
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('subjects') || !Schema::hasColumn('subjects', 'syllabus_url')) {
            return;
        }

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('syllabus_url');
        });
    }
};

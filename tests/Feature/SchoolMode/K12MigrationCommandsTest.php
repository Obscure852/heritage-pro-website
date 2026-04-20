<?php

namespace Tests\Feature\SchoolMode;

use App\Models\SchoolSetup;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\EnsuresPreF3SchoolModeSchema;
use Tests\TestCase;

class K12MigrationCommandsTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPreF3SchoolModeSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensurePreF3SchoolModeSchema();
        $this->ensureMigrationTables();
        $this->resetPreF3SchoolModeTables();
        $this->resetMigrationTables();

        DB::table('school_setup')->insert([
            'id' => 1,
            'school_name' => 'Migration Test School',
            'type' => SchoolSetup::TYPE_PRIMARY,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('terms')->insert([
            'id' => 1,
            'term' => 1,
            'year' => 2026,
            'start_date' => '2026-01-10',
            'end_date' => '2026-04-10',
            'closed' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session(['selected_term_id' => 1]);
    }

    public function test_k12_migration_preview_reports_readiness_metrics(): void
    {
        DB::table('grades')->insert([
            'id' => 1,
            'sequence' => 12,
            'name' => 'F4',
            'promotion' => 'F5',
            'description' => 'Form 4',
            'level' => SchoolSetup::LEVEL_SENIOR,
            'active' => true,
            'term_id' => 1,
            'year' => 2026,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('value_addition_subject_mappings')->insert([
            'school_type' => SchoolSetup::TYPE_SENIOR,
            'exam_type' => 'JCE',
            'source_key' => 'english',
            'source_label' => 'English',
            'subject_id' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $exitCode = Artisan::call('k12:migration:preview');

        $this->assertSame(0, $exitCode);

        $output = Artisan::output();
        $this->assertStringContainsString('K12 migration readiness preview', $output);
        $this->assertStringContainsString('senior_grades', $output);
        $this->assertStringContainsString('jce_value_addition_mappings', $output);
    }

    public function test_k12_migration_run_initializes_batch_and_provisions_target_structure(): void
    {
        $exitCode = Artisan::call('k12:migration:run');

        $this->assertSame(0, $exitCode);
        $this->assertSame(SchoolSetup::TYPE_K12, DB::table('school_setup')->value('type'));
        $this->assertDatabaseHas('migration_batches', [
            'mode' => SchoolSetup::TYPE_K12,
            'status' => 'prepared',
        ]);
        $this->assertDatabaseHas('grades', [
            'name' => 'F5',
            'level' => SchoolSetup::LEVEL_SENIOR,
        ]);
    }

    private function ensureMigrationTables(): void
    {
        if (!Schema::hasTable('migration_batches')) {
            Schema::create('migration_batches', function (Blueprint $table): void {
                $table->id();
                $table->string('mode')->index();
                $table->string('status')->default('pending')->index();
                $table->json('summary')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('migration_id_maps')) {
            Schema::create('migration_id_maps', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('migration_batch_id');
                $table->string('entity_type')->index();
                $table->string('source_system')->index();
                $table->string('source_identifier');
                $table->unsignedBigInteger('target_identifier')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('migration_conflicts')) {
            Schema::create('migration_conflicts', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('migration_batch_id');
                $table->string('entity_type')->index();
                $table->string('conflict_type')->index();
                $table->string('status')->default('open')->index();
                $table->json('details');
                $table->timestamps();
            });
        }
    }

    private function resetMigrationTables(): void
    {
        foreach (['migration_conflicts', 'migration_id_maps', 'migration_batches'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }
}

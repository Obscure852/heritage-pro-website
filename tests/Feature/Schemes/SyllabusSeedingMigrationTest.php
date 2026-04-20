<?php

namespace Tests\Feature\Schemes;

use App\Models\Schemes\Syllabus;
use App\Models\Subject;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SyllabusSeedingMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_migrations_seed_subject_urls_and_canonical_syllabi_for_senior_subjects(): void
    {
        $this->seedSubjectCatalog([
            ['abbrev' => 'ENG', 'name' => 'English', 'level' => 'Senior', 'department' => 'Languages'],
            ['abbrev' => 'EL', 'name' => 'English Literature', 'level' => 'Senior', 'department' => 'Languages'],
            ['abbrev' => 'DS', 'name' => 'Double Science', 'level' => 'Senior', 'department' => 'Sciences'],
            ['abbrev' => 'AGR', 'name' => 'Agriculture', 'level' => 'Senior', 'department' => 'Sciences'],
        ]);
        $this->runSyllabusSeedMigrations();

        $english = Subject::query()->where('level', 'Senior')->where('abbrev', 'ENG')->firstOrFail();
        $literature = Subject::query()->where('level', 'Senior')->where('abbrev', 'EL')->firstOrFail();
        $doubleScience = Subject::query()->where('level', 'Senior')->where('abbrev', 'DS')->firstOrFail();
        $agriculture = Subject::query()->where('level', 'Senior')->where('abbrev', 'AGR')->firstOrFail();

        $this->assertSame(
            'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/English_Language_Syllabus.json',
            $english->syllabus_url
        );
        $this->assertSame(
            'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Literature_in_English_Syllabus.json',
            $literature->syllabus_url
        );
        $this->assertSame(
            'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/science_double_award_syllabus.json',
            $doubleScience->syllabus_url
        );
        $this->assertNull($agriculture->syllabus_url);

        $englishSyllabus = Syllabus::query()
            ->where('subject_id', $english->id)
            ->where('level', 'Senior')
            ->whereNull('deleted_at')
            ->firstOrFail();

        $this->assertSame(['F4', 'F5'], $englishSyllabus->grades);
        $this->assertSame($english->syllabus_url, $englishSyllabus->source_url);
        $this->assertTrue($englishSyllabus->is_active);
        $this->assertNotNull($englishSyllabus->document_id);
        $this->assertNull($englishSyllabus->description);
        $this->assertNull($englishSyllabus->cached_structure);
        $this->assertNull($englishSyllabus->cached_at);

        $linkedDocument = Document::query()->findOrFail($englishSyllabus->document_id);
        $this->assertSame(Document::SOURCE_EXTERNAL_URL, $linkedDocument->source_type);
        $this->assertSame(
            'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/documents/English+Language.pdf',
            $linkedDocument->external_url
        );
        $this->assertSame('pdf', $linkedDocument->extension);
        $this->assertNotNull($linkedDocument->folder_id);
        $this->assertSame(Document::STATUS_PUBLISHED, $linkedDocument->status);
        $this->assertSame(Document::VISIBILITY_INTERNAL, $linkedDocument->visibility);
        $this->assertSame(
            \App\Support\SyllabusDocumentSync::FOLDER_NAME,
            DB::table('document_folders')->where('id', $linkedDocument->folder_id)->value('name')
        );
    }

    public function test_subject_syllabus_url_adjustment_backfills_matched_existing_rows(): void
    {
        $this->seedSubjectCatalog([
            ['abbrev' => 'EL', 'name' => 'English Literature', 'level' => 'Senior', 'department' => 'Languages'],
            ['abbrev' => 'DS', 'name' => 'Double Science', 'level' => 'Senior', 'department' => 'Sciences'],
        ]);
        $migration = require database_path('migrations/2026_03_08_000001_add_syllabus_url_to_subjects_table.php');
        $migration->up();

        $literature = Subject::query()->where('level', 'Senior')->where('abbrev', 'EL')->firstOrFail();
        $doubleScience = Subject::query()->where('level', 'Senior')->where('abbrev', 'DS')->firstOrFail();

        $literature->update(['syllabus_url' => null]);
        $doubleScience->update(['syllabus_url' => null]);

        $juniorEnglish = Subject::create([
            'abbrev' => 'ENG',
            'name' => 'English',
            'level' => 'Junior',
            'components' => false,
            'description' => null,
            'department' => 'Languages',
            'syllabus_url' => null,
        ]);

        $juniorOfficeProcedures = Subject::create([
            'abbrev' => 'OP',
            'name' => 'Office Procedures',
            'level' => 'Junior',
            'components' => false,
            'description' => null,
            'department' => 'Office Procedures',
            'syllabus_url' => null,
        ]);

        $migration = require database_path('migrations/2026_03_08_000001_add_syllabus_url_to_subjects_table.php');
        $migration->up();

        $this->assertSame(
            'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/Literature_in_English_Syllabus.json',
            $literature->fresh()->syllabus_url
        );
        $this->assertSame(
            'https://bw-syllabus.s3.us-east-1.amazonaws.com/ss-subjects/science_double_award_syllabus.json',
            $doubleScience->fresh()->syllabus_url
        );
        $this->assertSame(
            'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/english_syllabus.json',
            $juniorEnglish->fresh()->syllabus_url
        );
        $this->assertSame(
            'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/office_procedures_syllabus.json',
            $juniorOfficeProcedures->fresh()->syllabus_url
        );
    }

    public function test_folder_name_fix_migration_renames_legacy_syllabus_documents_folder(): void
    {
        $folderId = DB::table('document_folders')
            ->where('name', \App\Support\SyllabusDocumentSync::FOLDER_NAME)
            ->whereNull('deleted_at')
            ->value('id');

        $this->assertNotNull($folderId);

        DB::table('document_folders')
            ->where('id', $folderId)
            ->update([
                'name' => \App\Support\SyllabusDocumentSync::LEGACY_FOLDER_NAME,
                'updated_at' => now(),
            ]);

        $migration = require database_path('migrations/2026_03_09_000004_fix_syllabus_documents_folder_name.php');
        $migration->up();

        $this->assertSame(
            \App\Support\SyllabusDocumentSync::FOLDER_NAME,
            DB::table('document_folders')->where('id', $folderId)->value('name')
        );
        $this->assertSame(
            0,
            DB::table('document_folders')
                ->where('name', \App\Support\SyllabusDocumentSync::LEGACY_FOLDER_NAME)
                ->whereNull('deleted_at')
                ->count()
        );
    }

    public function test_canonical_syllabus_adjustment_replaces_existing_active_rows_and_is_idempotent(): void
    {
        $subject = Subject::create([
            'abbrev' => 'ENG',
            'name' => 'English',
            'level' => 'Junior',
            'components' => false,
            'description' => null,
            'department' => 'Languages',
            'syllabus_url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/english_syllabus.json',
        ]);

        DB::table('syllabi')->insert([
            [
                'subject_id' => $subject->id,
                'grades' => json_encode(['F1'], JSON_UNESCAPED_SLASHES),
                'level' => 'Junior',
                'document_id' => null,
                'is_active' => true,
                'description' => 'Legacy row one',
                'source_url' => 'https://old.example.com/legacy-one.json',
                'cached_structure' => null,
                'cached_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'subject_id' => $subject->id,
                'grades' => json_encode(['F2'], JSON_UNESCAPED_SLASHES),
                'level' => 'Junior',
                'document_id' => null,
                'is_active' => true,
                'description' => 'Legacy row two',
                'source_url' => 'https://old.example.com/legacy-two.json',
                'cached_structure' => null,
                'cached_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $migration = require database_path('migrations/2026_03_08_000002_seed_canonical_syllabi_from_subject_urls.php');
        $migration->up();

        $activeRows = Syllabus::query()
            ->where('subject_id', $subject->id)
            ->where('level', 'Junior')
            ->whereNull('deleted_at')
            ->get();

        $this->assertCount(1, $activeRows);
        $this->assertSame(['F1', 'F2', 'F3'], $activeRows->first()->grades);
        $this->assertSame($subject->syllabus_url, $activeRows->first()->source_url);
        $this->assertNull($activeRows->first()->description);
        $this->assertSame(
            2,
            Syllabus::withTrashed()
                ->where('subject_id', $subject->id)
                ->where('level', 'Junior')
                ->whereNotNull('deleted_at')
                ->count()
        );

        $migration = require database_path('migrations/2026_03_08_000002_seed_canonical_syllabi_from_subject_urls.php');
        $migration->up();

        $this->assertCount(
            1,
            Syllabus::query()
                ->where('subject_id', $subject->id)
                ->where('level', 'Junior')
                ->whereNull('deleted_at')
                ->get()
        );
        $this->assertSame(
            3,
            Syllabus::withTrashed()
                ->where('subject_id', $subject->id)
                ->where('level', 'Junior')
                ->count()
        );
    }

    /**
     * @param array<int, array{abbrev:string,name:string,level:string,department:string}> $subjects
     */
    private function seedSubjectCatalog(array $subjects): void
    {
        foreach ($subjects as $subject) {
            Subject::query()->firstOrCreate(
                [
                    'level' => $subject['level'],
                    'abbrev' => $subject['abbrev'],
                ],
                [
                    'name' => $subject['name'],
                    'components' => false,
                    'description' => null,
                    'department' => $subject['department'],
                    'syllabus_url' => null,
                ]
            );
        }
    }

    private function runSyllabusSeedMigrations(): void
    {
        $migration = require database_path('migrations/2026_03_08_000001_add_syllabus_url_to_subjects_table.php');
        $migration->up();

        $migration = require database_path('migrations/2026_03_08_000002_seed_canonical_syllabi_from_subject_urls.php');
        $migration->up();

        $migration = require database_path('migrations/2026_03_09_000002_seed_canonical_syllabus_documents.php');
        $migration->up();

        $migration = require database_path('migrations/2026_03_09_000003_refresh_syllabus_documents_with_pdf_urls.php');
        $migration->up();
    }
}

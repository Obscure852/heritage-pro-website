<?php

namespace Tests\Feature\SchoolMode;

use App\Http\Controllers\AssessmentController;
use App\Jobs\SendBulkReportCards;
use App\Models\SchoolSetup;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\EnsuresPreF3SchoolModeSchema;
use Tests\TestCase;

class BulkReportCardEmailTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPreF3SchoolModeSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        $this->ensurePreF3SchoolModeSchema();
        $this->ensureRoleTables();
        $this->resetPreF3SchoolModeTables();
        $this->resetRoleTables();
        Cache::flush();

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

        DB::table('school_setup')->insert([
            'id' => 1,
            'school_name' => 'Combined School',
            'type' => SchoolSetup::TYPE_PRE_F3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => 'admin@example.com',
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'year' => 2026,
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('roles')->insert([
            'id' => 1,
            'name' => 'Administrator',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('role_users')->insert([
            'user_id' => 1,
            'role_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('grades')->insert([
            'id' => 1,
            'sequence' => 2,
            'name' => 'STD 1',
            'promotion' => 'STD 2',
            'description' => 'Standard 1',
            'level' => SchoolSetup::LEVEL_PRIMARY,
            'active' => true,
            'term_id' => 1,
            'year' => 2026,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session(['selected_term_id' => 1]);
        $this->actingAs(\App\Models\User::findOrFail(1));
    }

    public function test_small_bulk_report_card_batch_sends_directly(): void
    {
        Queue::fake();
        Mail::shouldReceive('send')->once();

        $studentId = $this->seedStudentWithSponsor(1);
        $fakePdf = new class {
            public function output(): string
            {
                return 'fake-pdf-bytes';
            }
        };

        $this->partialMock(AssessmentController::class, function ($mock) use ($fakePdf): void {
            $mock->shouldReceive('prepareReportCardPdfForStudent')
                ->once()
                ->andReturn([
                    'pdf' => $fakePdf,
                    'filename' => 'student_report_card.pdf',
                ]);
        });

        $response = $this->postJson(route('assessment.bulk-email-report-cards'), [
            'bulkSubject' => 'Term 1 Report Card',
            'bulkMessage' => 'Attached is your child report card.',
            'students' => [$studentId],
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'sent',
                'count' => 1,
            ]);

        $this->assertStringContainsString('sent directly', $response->json('message'));
        Queue::assertNothingPushed();
        $this->assertDatabaseCount('emails', 1);
    }

    public function test_large_bulk_report_card_batch_queues_jobs(): void
    {
        Queue::fake();

        $studentIds = [];
        foreach (range(1, 11) as $index) {
            $studentIds[] = $this->seedStudentWithSponsor($index);
        }

        $response = $this->postJson(route('assessment.bulk-email-report-cards'), [
            'bulkSubject' => 'Term 1 Report Card',
            'bulkMessage' => 'Attached is your child report card.',
            'students' => $studentIds,
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'queued',
                'count' => 11,
            ]);

        $this->assertStringContainsString('queued successfully', $response->json('message'));
        Queue::assertPushed(SendBulkReportCards::class, 11);
    }

    private function seedStudentWithSponsor(int $id): int
    {
        DB::table('sponsors')->insert([
            'id' => $id,
            'connect_id' => 1000 + $id,
            'first_name' => 'Parent',
            'last_name' => (string) $id,
            'email' => "parent{$id}@example.com",
            'year' => 2026,
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('students')->insert([
            'id' => $id,
            'connect_id' => 2000 + $id,
            'sponsor_id' => $id,
            'first_name' => 'Student',
            'last_name' => (string) $id,
            'gender' => 'M',
            'date_of_birth' => '2015-01-01',
            'nationality' => 'Motswana',
            'id_number' => 'ID-' . $id,
            'status' => 'Current',
            'type' => 'Current',
            'year' => 2026,
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('student_term')->insert([
            'student_id' => $id,
            'term_id' => 1,
            'grade_id' => 1,
            'year' => 2026,
            'status' => 'Current',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function ensureRoleTables(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_users')) {
            Schema::create('role_users', function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->timestamps();
            });
        }
    }

    private function resetRoleTables(): void
    {
        DB::table('role_users')->delete();
        DB::table('roles')->delete();
    }
}

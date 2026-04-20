<?php

namespace Tests\Feature\Invigilation;

use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\SchoolSetup;
use App\Models\SMSApiSetting;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\Venue;
use App\Models\Invigilation\InvigilationSeries;
use App\Models\Invigilation\InvigilationSessionRoom;
use App\Services\Invigilation\InvigilationPublishNotificationService;
use App\Services\SettingsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\EnsuresInvigilationSchema;
use Tests\TestCase;

class InvigilationPublishNotificationServiceTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresInvigilationSchema;

    private InvigilationPublishNotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureInvigilationSchema();
        $this->ensureMessagingSchema();
        $this->service = app(InvigilationPublishNotificationService::class);
        DB::table('staff_direct_messages')->delete();
        DB::table('staff_direct_conversations')->delete();

        SchoolSetup::query()->updateOrCreate(
            ['id' => 1],
            [
                'school_name' => 'Merementsi Junior Secondary School',
                'type' => SchoolSetup::TYPE_JUNIOR,
            ]
        );

        $this->setDirectMessagesEnabled(true);
    }

    public function test_publish_notifications_send_one_direct_message_per_assigned_teacher_with_duty_summary(): void
    {
        [$term, $gradeSubject] = $this->createAcademicContext();
        $actor = $this->createUser('Publisher', 'Admin', 'publish-admin@example.com', 'Administration');
        $teacherAlpha = $this->createUser('Teacher', 'Alpha', 'teacher-alpha@example.com');
        $teacherBravo = $this->createUser('Teacher', 'Bravo', 'teacher-bravo@example.com');

        $series = $this->createSeries($term, 'June Final Exams');

        $firstRoom = $this->createManualRoom($series, $gradeSubject, $this->createVenue('Hall A'), '2026-06-10', '08:00', '09:00', 'Group A');
        $secondRoom = $this->createManualRoom($series, $gradeSubject, $this->createVenue('Hall B'), '2026-06-10', '09:30', '10:30', 'Group B');
        $thirdRoom = $this->createManualRoom($series, $gradeSubject, $this->createVenue('Hall C'), '2026-06-11', '11:00', '12:00', 'Group C');

        $firstRoom->assignments()->create([
            'user_id' => $teacherAlpha->id,
            'assignment_order' => 1,
            'assignment_source' => 'manual',
            'locked' => true,
        ]);
        $secondRoom->assignments()->create([
            'user_id' => $teacherBravo->id,
            'assignment_order' => 1,
            'assignment_source' => 'auto',
            'locked' => false,
        ]);
        $thirdRoom->assignments()->create([
            'user_id' => $teacherAlpha->id,
            'assignment_order' => 1,
            'assignment_source' => 'manual',
            'locked' => true,
        ]);

        $summary = $this->service->notifyAssignedTeachers($series->fresh(), $actor);

        $this->assertTrue($summary['enabled']);
        $this->assertSame(2, $summary['recipient_count']);
        $this->assertSame(2, $summary['sent_count']);
        $this->assertSame(0, $summary['skipped_count']);
        $this->assertSame(0, $summary['failed_count']);
        $this->assertDatabaseCount('staff_direct_conversations', 2);
        $this->assertDatabaseCount('staff_direct_messages', 2);

        $alphaMessageBody = DB::table('staff_direct_messages')
            ->join('staff_direct_conversations', 'staff_direct_conversations.id', '=', 'staff_direct_messages.conversation_id')
            ->where(function ($query) use ($actor, $teacherAlpha) {
                $query->where('staff_direct_conversations.user_one_id', min($actor->id, $teacherAlpha->id))
                    ->where('staff_direct_conversations.user_two_id', max($actor->id, $teacherAlpha->id));
            })
            ->value('body');

        $bravoMessageBody = DB::table('staff_direct_messages')
            ->join('staff_direct_conversations', 'staff_direct_conversations.id', '=', 'staff_direct_messages.conversation_id')
            ->where(function ($query) use ($actor, $teacherBravo) {
                $query->where('staff_direct_conversations.user_one_id', min($actor->id, $teacherBravo->id))
                    ->where('staff_direct_conversations.user_two_id', max($actor->id, $teacherBravo->id));
            })
            ->value('body');

        $this->assertIsString($alphaMessageBody);
        $this->assertStringContainsString('June Final Exams', $alphaMessageBody);
        $this->assertStringContainsString('Hall A', $alphaMessageBody);
        $this->assertStringContainsString('Hall C', $alphaMessageBody);
        $this->assertStringContainsString(route('invigilation.view.teacher-roster', ['series_id' => $series->id]), $alphaMessageBody);

        $this->assertIsString($bravoMessageBody);
        $this->assertStringContainsString('Hall B', $bravoMessageBody);
        $this->assertStringNotContainsString('Hall A', $bravoMessageBody);
    }

    public function test_publish_notifications_respect_direct_message_feature_toggle(): void
    {
        [$term, $gradeSubject] = $this->createAcademicContext();
        $actor = $this->createUser('Publisher', 'Admin', 'publish-admin-disabled@example.com', 'Administration');
        $teacher = $this->createUser('Teacher', 'Disabled', 'teacher-disabled@example.com');
        $series = $this->createSeries($term, 'Disabled Message Series');

        $room = $this->createManualRoom($series, $gradeSubject, $this->createVenue('Hall Disabled'), '2026-06-12', '08:00', '09:00', 'Group D');
        $room->assignments()->create([
            'user_id' => $teacher->id,
            'assignment_order' => 1,
            'assignment_source' => 'manual',
            'locked' => true,
        ]);

        $this->setDirectMessagesEnabled(false);

        $summary = $this->service->notifyAssignedTeachers($series->fresh(), $actor);

        $this->assertFalse($summary['enabled']);
        $this->assertSame(0, $summary['sent_count']);
        $this->assertDatabaseCount('staff_direct_conversations', 0);
        $this->assertDatabaseCount('staff_direct_messages', 0);
    }

    private function ensureMessagingSchema(): void
    {
        if (!Schema::hasColumn('users', 'active')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->boolean('active')->default(true);
            });
        }

        if (!Schema::hasTable('s_m_s_api_settings')) {
            Schema::create('s_m_s_api_settings', function (Blueprint $table): void {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('category')->nullable();
                $table->string('type')->default('string');
                $table->text('description')->nullable();
                $table->string('display_name')->nullable();
                $table->string('validation_rules')->nullable();
                $table->boolean('is_editable')->default(true);
                $table->unsignedInteger('display_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('staff_direct_conversations')) {
            Schema::create('staff_direct_conversations', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_one_id');
                $table->unsignedBigInteger('user_two_id');
                $table->timestamp('last_message_at')->nullable();
                $table->timestamp('user_one_read_at')->nullable();
                $table->timestamp('user_two_read_at')->nullable();
                $table->unsignedBigInteger('user_one_last_read_message_id')->nullable();
                $table->unsignedBigInteger('user_two_last_read_message_id')->nullable();
                $table->boolean('is_archived_by_user_one')->default(false);
                $table->boolean('is_archived_by_user_two')->default(false);
                $table->timestamps();
                $table->unique(['user_one_id', 'user_two_id'], 'staff_direct_unique_pair');
            });
        }

        if (!Schema::hasTable('staff_direct_messages')) {
            Schema::create('staff_direct_messages', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('conversation_id');
                $table->unsignedBigInteger('sender_id');
                $table->text('body');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    private function setDirectMessagesEnabled(bool $enabled): void
    {
        SMSApiSetting::query()->updateOrCreate(
            ['key' => 'features.staff_direct_messages_enabled'],
            [
                'value' => $enabled ? '1' : '0',
                'category' => 'feature',
                'type' => 'boolean',
                'display_name' => 'Staff Direct Messages',
                'is_editable' => true,
                'display_order' => 1,
            ]
        );

        app(SettingsService::class)->refresh('features.staff_direct_messages_enabled');
    }

    private function createAcademicContext(): array
    {
        $term = Term::query()->updateOrCreate(
            ['term' => 1, 'year' => 2026],
            [
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'closed' => false,
            ]
        );

        $grade = Grade::query()->create([
            'name' => 'Form 1',
            'sequence' => 1,
            'promotion' => 'Form 2',
            'description' => 'Junior grade',
            'level' => SchoolSetup::LEVEL_JUNIOR,
            'active' => true,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Mathematics',
            'abbrev' => 'MATH',
            'canonical_key' => 'math_' . uniqid(),
            'level' => SchoolSetup::LEVEL_JUNIOR,
            'components' => false,
        ]);

        $departmentId = DB::table('departments')->where('name', 'Academic')->value('id');
        if (!$departmentId) {
            $departmentId = DB::table('departments')->insertGetId([
                'name' => 'Academic',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('grade_subject')->updateOrInsert(
            [
                'term_id' => $term->id,
                'grade_id' => $grade->id,
                'subject_id' => $subject->id,
            ],
            [
                'department_id' => $departmentId,
                'year' => $term->year,
                'sequence' => 1,
                'type' => 'core',
                'mandatory' => true,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]
        );

        $gradeSubject = GradeSubject::query()
            ->where('term_id', $term->id)
            ->where('grade_id', $grade->id)
            ->where('subject_id', $subject->id)
            ->firstOrFail();

        return [$term, $gradeSubject];
    }

    private function createSeries(Term $term, string $name): InvigilationSeries
    {
        return InvigilationSeries::query()->create([
            'name' => $name,
            'type' => InvigilationSeries::TYPE_FINAL,
            'term_id' => $term->id,
            'status' => InvigilationSeries::STATUS_PUBLISHED,
            'eligibility_policy' => InvigilationSeries::POLICY_ANY_TEACHER,
            'timetable_conflict_policy' => InvigilationSeries::TIMETABLE_IGNORE,
            'balancing_policy' => 'balanced',
            'default_required_invigilators' => 1,
            'published_at' => now(),
            'published_by' => null,
        ]);
    }

    private function createUser(string $firstname, string $lastname, string $email, string $areaOfWork = 'Teaching'): User
    {
        return User::withoutEvents(fn () => User::query()->create([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'password' => 'secret',
            'active' => true,
            'status' => 'Current',
            'position' => 'Teacher',
            'area_of_work' => $areaOfWork,
            'year' => 2026,
        ]));
    }

    private function createVenue(string $name): Venue
    {
        return Venue::query()->create([
            'name' => $name,
            'type' => 'Hall',
            'capacity' => 40,
        ]);
    }

    private function createManualRoom(
        InvigilationSeries $series,
        GradeSubject $gradeSubject,
        Venue $venue,
        string $examDate,
        string $startTime,
        string $endTime,
        string $groupLabel
    ): InvigilationSessionRoom {
        $session = $series->sessions()->create([
            'grade_subject_id' => $gradeSubject->id,
            'paper_label' => 'Paper 1',
            'exam_date' => $examDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        return $session->rooms()->create([
            'venue_id' => $venue->id,
            'source_type' => InvigilationSessionRoom::SOURCE_MANUAL,
            'group_label' => $groupLabel,
            'candidate_count' => 20,
            'required_invigilators' => 1,
        ]);
    }
}

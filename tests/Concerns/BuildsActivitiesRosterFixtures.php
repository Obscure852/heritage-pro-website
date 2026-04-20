<?php

namespace Tests\Concerns;

use App\Models\Activities\Activity;
use App\Models\Activities\ActivityEligibilityTarget;
use App\Models\Activities\ActivityStaffAssignment;
use App\Models\Activities\ActivitySchedule;
use App\Models\Activities\ActivitySession;
use App\Models\Activities\ActivityEnrollment;
use App\Models\Activities\ActivityEvent;
use App\Models\Activities\ActivityResult;
use App\Models\Grade;
use App\Models\House;
use App\Models\Klass;
use App\Models\Fee\FeeType;
use App\Models\Fee\StudentInvoice;
use App\Models\Role;
use App\Models\Sponsor;
use App\Models\Student;
use App\Models\StudentFilter;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait BuildsActivitiesRosterFixtures
{
    protected function seedActivitiesSchoolSetup(): void
    {
        DB::table('school_setup')->updateOrInsert(
            ['id' => 1],
            [
                'school_name' => 'Merementsi Junior Secondary School',
                'type' => 'Junior',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    protected function createActivityUser(string $email, array $roles, array $overrides = []): User
    {
        $resolvedEmail = $email;

        if (User::query()->where('email', $resolvedEmail)->exists()) {
            [$localPart, $domainPart] = array_pad(explode('@', $resolvedEmail, 2), 2, 'example.com');
            $resolvedEmail = sprintf('%s+%s@%s', $localPart, uniqid('activity-', true), $domainPart);
        }

        $user = User::withoutEvents(fn () => User::query()->create(array_merge([
            'firstname' => 'Activity',
            'lastname' => 'Roster Tester',
            'email' => $resolvedEmail,
            'password' => 'secret',
            'status' => 'Current',
            'position' => 'Teacher',
            'year' => 2026,
        ], $overrides)));

        $roleIds = collect($roles)
            ->map(fn (string $name): int => (int) Role::query()->firstOrCreate(
                ['name' => $name],
                ['description' => $name]
            )->id)
            ->all();

        $user->roles()->syncWithoutDetaching($roleIds);

        return $user->fresh();
    }

    protected function createActivityTerm(int $year, int $termNumber): Term
    {
        $attributes = [
            'term' => $termNumber,
            'year' => $year,
            'start_date' => sprintf('%d-0%d-01', $year, max(1, min(9, $termNumber))),
            'end_date' => sprintf('%d-0%d-28', $year, max(1, min(9, $termNumber))),
            'closed' => false,
        ];

        if (Schema::hasColumn('terms', 'term_type')) {
            $attributes['term_type'] = 'Academic';
        }

        return Term::query()->firstOrCreate(
            [
                'term' => $termNumber,
                'year' => $year,
            ],
            $attributes
        );
    }

    protected function createActivityRecord(Term $term, User $user, array $overrides = []): Activity
    {
        $requestedCode = strtoupper((string) ($overrides['code'] ?? ('ACT' . strtoupper(substr(md5(uniqid((string) $term->id, true)), 0, 6)))));
        $resolvedCode = $requestedCode;

        if (Activity::query()->where('year', $term->year)->where('code', $resolvedCode)->exists()) {
            $resolvedCode = substr($requestedCode, 0, 18) . strtoupper(substr(md5(uniqid((string) $term->id, true)), 0, 6));
        }

        $payload = array_merge([
            'name' => 'Chess Tournament',
            'code' => $resolvedCode,
            'category' => Activity::CATEGORY_CLUB,
            'delivery_mode' => Activity::DELIVERY_RECURRING,
            'participation_mode' => Activity::PARTICIPATION_TEAM,
            'result_mode' => Activity::RESULT_MIXED,
            'description' => 'Phase 3 test activity.',
            'default_location' => 'School Hall',
            'capacity' => 40,
            'gender_policy' => 'mixed',
            'attendance_required' => true,
            'allow_house_linkage' => true,
            'status' => Activity::STATUS_DRAFT,
            'term_id' => $term->id,
            'year' => $term->year,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ], $overrides);

        $payload['code'] = $resolvedCode;

        return Activity::query()->create($payload);
    }

    protected function createGradeForTerm(Term $term, string $name = 'F1', int $sequence = 1): Grade
    {
        return Grade::query()->create([
            'sequence' => $sequence,
            'name' => $name,
            'promotion' => 'Yes',
            'description' => $name . ' grade',
            'level' => 'Junior',
            'active' => true,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);
    }

    protected function createKlassForTerm(Term $term, Grade $grade, User $teacher, string $name = 'F1 Blue'): Klass
    {
        return Klass::query()->create([
            'name' => $name,
            'user_id' => $teacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'type' => true,
            'year' => $term->year,
        ]);
    }

    protected function createHouseForTerm(Term $term, User $head, User $assistant, string $name = 'Kgosi'): House
    {
        return House::query()->create([
            'name' => $name,
            'head' => $head->id,
            'assistant' => $assistant->id,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);
    }

    protected function createStudentFilter(string $name = 'Boarders'): StudentFilter
    {
        return StudentFilter::query()->create([
            'name' => $name,
        ]);
    }

    protected function createStudentForActivity(
        Term $term,
        Grade $grade,
        ?Klass $klass = null,
        ?House $house = null,
        ?StudentFilter $studentFilter = null,
        array $overrides = []
    ): Student {
        $sponsor = $this->createSponsorRecord($term->year);

        $student = Student::withoutEvents(fn () => Student::query()->create(array_merge([
            'sponsor_id' => $sponsor->id,
            'first_name' => 'Student',
            'last_name' => 'Example',
            'status' => Student::STATUS_CURRENT,
            'gender' => Student::GENDER_MALE,
            'date_of_birth' => '2012-01-01',
            'nationality' => 'Botswana',
            'id_number' => 'STU' . strtoupper(substr(md5(uniqid((string) $term->id, true)), 0, 10)),
            'student_filter_id' => $studentFilter?->id,
            'year' => $term->year,
            'password' => 'secret',
        ], $overrides)));

        DB::table('student_term')->insert([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => $term->year,
            'status' => Student::STATUS_CURRENT,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        if ($klass) {
            DB::table('klass_student')->insert([
                'klass_id' => $klass->id,
                'student_id' => $student->id,
                'term_id' => $term->id,
                'grade_id' => $grade->id,
                'year' => $term->year,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($house) {
            DB::table('student_house')->insert([
                'student_id' => $student->id,
                'house_id' => $house->id,
                'term_id' => $term->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $student->fresh();
    }

    protected function createSponsorRecord(int $year): Sponsor
    {
        return Sponsor::withoutEvents(fn () => Sponsor::query()->create([
            'connect_id' => random_int(1000, 999999),
            'first_name' => 'Parent',
            'last_name' => 'Support',
            'email' => 'sponsor-' . uniqid() . '@example.com',
            'gender' => 'F',
            'date_of_birth' => '1984-01-01',
            'nationality' => 'Botswana',
            'relation' => 'Parent',
            'status' => 'Current',
            'id_number' => 'SPO' . strtoupper(substr(md5(uniqid((string) $year, true)), 0, 10)),
            'phone' => '71000000',
            'year' => $year,
            'password' => 'secret',
        ]));
    }

    protected function createActivityFeeType(array $overrides = []): FeeType
    {
        return FeeType::query()->create(array_merge([
            'code' => 'ACT-' . strtoupper(substr(md5(uniqid('fee', true)), 0, 6)),
            'name' => 'Activity Fee',
            'category' => FeeType::CATEGORY_OPTIONAL,
            'description' => 'Activities fee type for phase six tests.',
            'is_optional' => true,
            'is_active' => true,
        ], $overrides));
    }

    protected function createStudentInvoice(Student $student, User $user, int $year, array $overrides = []): StudentInvoice
    {
        return StudentInvoice::query()->create(array_merge([
            'invoice_number' => 'INV-' . $year . '-' . strtoupper(substr(md5(uniqid((string) $student->id, true)), 0, 6)),
            'student_id' => $student->id,
            'year' => $year,
            'subtotal_amount' => '0.00',
            'discount_amount' => '0.00',
            'total_amount' => '0.00',
            'amount_paid' => '0.00',
            'balance' => '0.00',
            'credit_balance' => '0.00',
            'status' => StudentInvoice::STATUS_ISSUED,
            'issued_at' => now(),
            'due_date' => now()->addDays(30)->toDateString(),
            'created_by' => $user->id,
            'notes' => 'Activities phase six test invoice.',
        ], $overrides));
    }

    protected function attachEligibilityTargets(Activity $activity, array $targets): void
    {
        foreach ($targets as $type => $ids) {
            foreach ($ids as $targetId) {
                ActivityEligibilityTarget::query()->create([
                    'activity_id' => $activity->id,
                    'target_type' => $type,
                    'target_id' => $targetId,
                ]);
            }
        }
    }

    protected function assignPrimaryCoordinator(Activity $activity, User $user): ActivityStaffAssignment
    {
        return ActivityStaffAssignment::query()->create([
            'activity_id' => $activity->id,
            'user_id' => $user->id,
            'role' => ActivityStaffAssignment::ROLE_COORDINATOR,
            'is_primary' => true,
            'active' => true,
            'assigned_at' => now(),
        ]);
    }

    protected function createActivityScheduleRecord(Activity $activity, array $overrides = []): ActivitySchedule
    {
        return ActivitySchedule::query()->create(array_merge([
            'activity_id' => $activity->id,
            'frequency' => ActivitySchedule::FREQUENCY_WEEKLY,
            'day_of_week' => 1,
            'start_time' => '15:00',
            'end_time' => '16:00',
            'start_date' => $activity->term?->start_date?->format('Y-m-d') ?? ($activity->year . '-01-01'),
            'end_date' => $activity->term?->end_date?->format('Y-m-d') ?? ($activity->year . '-03-31'),
            'location' => $activity->default_location,
            'notes' => 'Recurring fixture schedule.',
            'active' => true,
        ], $overrides));
    }

    protected function createActivitySessionRecord(Activity $activity, ?ActivitySchedule $schedule = null, array $overrides = []): ActivitySession
    {
        $sessionDate = $overrides['session_date'] ?? ($activity->term?->start_date?->format('Y-m-d') ?? ($activity->year . '-01-08'));
        $startDateTime = $overrides['start_datetime'] ?? ($sessionDate . ' 15:00:00');
        $endDateTime = $overrides['end_datetime'] ?? ($sessionDate . ' 16:00:00');

        return ActivitySession::query()->create(array_merge([
            'activity_id' => $activity->id,
            'activity_schedule_id' => $schedule?->id,
            'session_type' => $schedule ? ActivitySession::TYPE_SCHEDULED : ActivitySession::TYPE_MANUAL,
            'session_date' => $sessionDate,
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'location' => $activity->default_location,
            'status' => ActivitySession::STATUS_PLANNED,
            'attendance_locked' => false,
            'notes' => 'Generated session.',
            'created_by' => $activity->created_by,
        ], $overrides));
    }

    protected function createActivityEnrollmentRecord(Activity $activity, Student $student, User $actor, array $overrides = []): ActivityEnrollment
    {
        return ActivityEnrollment::query()->create(array_merge([
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'term_id' => $activity->term_id,
            'year' => $activity->year,
            'status' => ActivityEnrollment::STATUS_ACTIVE,
            'joined_at' => now(),
            'joined_by' => $actor->id,
            'source' => ActivityEnrollment::SOURCE_MANUAL,
            'grade_id_snapshot' => $overrides['grade_id_snapshot'] ?? DB::table('student_term')
                ->where('student_id', $student->id)
                ->where('term_id', $activity->term_id)
                ->value('grade_id'),
            'klass_id_snapshot' => $overrides['klass_id_snapshot'] ?? DB::table('klass_student')
                ->where('student_id', $student->id)
                ->where('term_id', $activity->term_id)
                ->value('klass_id'),
            'house_id_snapshot' => $overrides['house_id_snapshot'] ?? DB::table('student_house')
                ->where('student_id', $student->id)
                ->where('term_id', $activity->term_id)
                ->value('house_id'),
        ], $overrides));
    }

    protected function createActivityEventRecord(Activity $activity, User $actor, array $overrides = []): ActivityEvent
    {
        $startDateTime = $overrides['start_datetime'] ?? (($activity->term?->start_date?->format('Y-m-d') ?? ($activity->year . '-01-15')) . ' 15:00:00');
        $endDateTime = $overrides['end_datetime'] ?? \Carbon\Carbon::parse($startDateTime)->addHour()->format('Y-m-d H:i:s');

        return ActivityEvent::query()->create(array_merge([
            'activity_id' => $activity->id,
            'title' => 'Inter-House Chess Fixture',
            'event_type' => ActivityEvent::TYPE_FIXTURE,
            'description' => 'Phase 5 event fixture.',
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'location' => $activity->default_location,
            'opponent_or_partner_name' => 'Partner School',
            'house_linked' => false,
            'publish_to_calendar' => false,
            'calendar_sync_status' => ActivityEvent::CALENDAR_NOT_PUBLISHED,
            'status' => ActivityEvent::STATUS_SCHEDULED,
            'created_by' => $actor->id,
        ], $overrides));
    }

    protected function createActivityResultRecord(ActivityEvent $event, User $actor, array $overrides = []): ActivityResult
    {
        return ActivityResult::query()->create(array_merge([
            'activity_event_id' => $event->id,
            'participant_type' => ActivityResult::PARTICIPANT_STUDENT,
            'participant_id' => 1,
            'metric_type' => ActivityResult::METRIC_MIXED,
            'score_value' => null,
            'placement' => 1,
            'points' => 3,
            'award_name' => 'Best Performer',
            'result_label' => 'Winner',
            'notes' => 'Recorded in fixture helper.',
            'recorded_by' => $actor->id,
        ], $overrides));
    }
}

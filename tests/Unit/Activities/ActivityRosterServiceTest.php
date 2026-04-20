<?php

namespace Tests\Unit\Activities;

use App\Models\Activities\ActivityEligibilityTarget;
use App\Models\Activities\ActivityEnrollment;
use App\Services\Activities\ActivityRosterService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\BuildsActivitiesRosterFixtures;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityRosterServiceTest extends TestCase
{
    use DatabaseTransactions;
    use BuildsActivitiesRosterFixtures;
    use EnsuresActivitiesPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureActivitiesPhaseOneSchema();
        $this->seedActivitiesSchoolSetup();
    }

    public function test_manual_enrollment_records_source_and_snapshots(): void
    {
        $admin = $this->createActivityUser('activities-service-admin@example.com', ['Activities Admin']);
        $assistant = $this->createActivityUser('activities-service-assistant@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 1);
        $grade = $this->createGradeForTerm($term);
        $klass = $this->createKlassForTerm($term, $grade, $admin);
        $house = $this->createHouseForTerm($term, $admin, $assistant);
        $activity = $this->createActivityRecord($term, $admin, ['capacity' => 4]);
        $student = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Service',
            'last_name' => 'Manual',
        ]);

        $enrollment = app(ActivityRosterService::class)->enrollStudent($activity, [
            'student_id' => $student->id,
            'joined_at' => '2026-01-10',
        ], $admin);

        $this->assertSame(ActivityEnrollment::SOURCE_MANUAL, $enrollment->source);
        $this->assertSame(ActivityEnrollment::STATUS_ACTIVE, $enrollment->status);
        $this->assertSame($grade->id, $enrollment->grade_id_snapshot);
        $this->assertSame($klass->id, $enrollment->klass_id_snapshot);
        $this->assertSame($house->id, $enrollment->house_id_snapshot);
    }

    public function test_bulk_preview_returns_only_eligible_students_without_active_enrollment(): void
    {
        $admin = $this->createActivityUser('activities-service-preview-admin@example.com', ['Activities Admin']);
        $assistant = $this->createActivityUser('activities-service-preview-assistant@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 2);
        $grade = $this->createGradeForTerm($term, 'F2');
        $klass = $this->createKlassForTerm($term, $grade, $admin, 'F2 Sky');
        $house = $this->createHouseForTerm($term, $admin, $assistant, 'Batawana');
        $filter = $this->createStudentFilter('Science');
        $otherFilter = $this->createStudentFilter('Arts');
        $activity = $this->createActivityRecord($term, $admin);

        $this->attachEligibilityTargets($activity, [
            ActivityEligibilityTarget::TARGET_GRADE => [$grade->id],
            ActivityEligibilityTarget::TARGET_CLASS => [$klass->id],
            ActivityEligibilityTarget::TARGET_HOUSE => [$house->id],
            ActivityEligibilityTarget::TARGET_STUDENT_FILTER => [$filter->id],
        ]);

        $eligibleStudent = $this->createStudentForActivity($term, $grade, $klass, $house, $filter, [
            'first_name' => 'Eligible',
            'last_name' => 'Preview',
        ]);

        $alreadyEnrolledStudent = $this->createStudentForActivity($term, $grade, $klass, $house, $filter, [
            'first_name' => 'Already',
            'last_name' => 'Enrolled',
        ]);

        $this->createStudentForActivity($term, $grade, $klass, $house, $otherFilter, [
            'first_name' => 'Wrong',
            'last_name' => 'Filter',
        ]);

        ActivityEnrollment::query()->create([
            'activity_id' => $activity->id,
            'student_id' => $alreadyEnrolledStudent->id,
            'term_id' => $term->id,
            'year' => $term->year,
            'status' => ActivityEnrollment::STATUS_ACTIVE,
            'joined_at' => now(),
            'joined_by' => $admin->id,
            'source' => ActivityEnrollment::SOURCE_MANUAL,
            'grade_id_snapshot' => $grade->id,
            'klass_id_snapshot' => $klass->id,
            'house_id_snapshot' => $house->id,
        ]);

        $preview = app(ActivityRosterService::class)->bulkEligibilityPreview($activity);

        $this->assertSame(1, $preview['count']);
        $this->assertSame($eligibleStudent->id, (int) $preview['students']->first()->id);
    }

    public function test_bulk_preview_matches_duplicate_same_name_house_rows_by_visible_house_selection(): void
    {
        $admin = $this->createActivityUser('activities-service-duplicate-house-admin@example.com', ['Activities Admin']);
        $assistant = $this->createActivityUser('activities-service-duplicate-house-assistant@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 2);
        $grade = $this->createGradeForTerm($term, 'F1');
        $klass = $this->createKlassForTerm($term, $grade, $admin, '1A');
        $selectedHouse = $this->createHouseForTerm($term, $admin, $assistant, 'Tawana');
        $duplicateHouse = $this->createHouseForTerm($term, $assistant, $admin, 'Tawana');
        $activity = $this->createActivityRecord($term, $admin);

        $this->attachEligibilityTargets($activity, [
            ActivityEligibilityTarget::TARGET_GRADE => [$grade->id],
            ActivityEligibilityTarget::TARGET_CLASS => [$klass->id],
            ActivityEligibilityTarget::TARGET_HOUSE => [$selectedHouse->id],
        ]);

        $eligibleStudent = $this->createStudentForActivity($term, $grade, $klass, $duplicateHouse, null, [
            'first_name' => 'Duplicate',
            'last_name' => 'House',
        ]);

        $preview = app(ActivityRosterService::class)->bulkEligibilityPreview($activity);

        $this->assertSame(1, $preview['count']);
        $this->assertSame($eligibleStudent->id, (int) $preview['students']->first()->id);
    }

    public function test_status_update_records_exit_metadata(): void
    {
        $admin = $this->createActivityUser('activities-service-status-admin@example.com', ['Activities Admin']);
        $assistant = $this->createActivityUser('activities-service-status-assistant@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 3);
        $grade = $this->createGradeForTerm($term, 'F3');
        $klass = $this->createKlassForTerm($term, $grade, $admin, 'F3 Silver');
        $house = $this->createHouseForTerm($term, $admin, $assistant, 'Bangwato');
        $activity = $this->createActivityRecord($term, $admin);
        $student = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Status',
            'last_name' => 'Update',
        ]);

        $enrollment = ActivityEnrollment::query()->create([
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'term_id' => $term->id,
            'year' => $term->year,
            'status' => ActivityEnrollment::STATUS_ACTIVE,
            'joined_at' => now(),
            'joined_by' => $admin->id,
            'source' => ActivityEnrollment::SOURCE_MANUAL,
            'grade_id_snapshot' => $grade->id,
            'klass_id_snapshot' => $klass->id,
            'house_id_snapshot' => $house->id,
        ]);

        $updated = app(ActivityRosterService::class)->updateEnrollmentStatus($activity, $enrollment, [
            'status' => ActivityEnrollment::STATUS_SUSPENDED,
            'left_at' => '2026-05-01',
            'exit_reason' => 'Pending conduct review.',
        ], $admin);

        $this->assertSame(ActivityEnrollment::STATUS_SUSPENDED, $updated->status);
        $this->assertSame('Pending conduct review.', $updated->exit_reason);
        $this->assertSame($admin->id, $updated->left_by);
        $this->assertSame('2026-05-01', optional($updated->left_at)->format('Y-m-d'));
    }
}

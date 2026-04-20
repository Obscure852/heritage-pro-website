<?php

namespace Tests\Unit\Activities;

use App\Models\Activities\Activity;
use App\Models\Activities\ActivityEvent;
use App\Models\Activities\ActivityResult;
use App\Services\Activities\ActivityResultService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\BuildsActivitiesRosterFixtures;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityResultServiceTest extends TestCase
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

    public function test_results_cannot_be_recorded_for_a_non_completed_event(): void
    {
        $admin = $this->createActivityUser('activities-result-service-admin@example.com', ['Activities Admin']);
        $assistant = $this->createActivityUser('activities-result-service-assistant@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 1);
        $grade = $this->createGradeForTerm($term, 'F1');
        $klass = $this->createKlassForTerm($term, $grade, $admin);
        $house = $this->createHouseForTerm($term, $admin, $assistant);
        $activity = $this->createActivityRecord($term, $admin, [
            'status' => Activity::STATUS_ACTIVE,
        ]);
        $student = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Naledi',
            'last_name' => 'Phiri',
        ]);
        $this->createActivityEnrollmentRecord($activity, $student, $admin, [
            'joined_at' => '2026-01-10 08:00:00',
        ]);
        $event = $this->createActivityEventRecord($activity, $admin, [
            'status' => ActivityEvent::STATUS_SCHEDULED,
            'start_datetime' => '2026-01-20 09:00:00',
        ]);

        $this->expectException(ValidationException::class);

        app(ActivityResultService::class)->syncResults($activity, $event, [
            'scope' => ActivityResult::PARTICIPANT_STUDENT,
            'results' => [
                $student->id => [
                    'selected' => true,
                    'result_label' => 'Winner',
                    'placement' => 1,
                    'points' => 5,
                ],
            ],
        ], $admin);
    }

    public function test_house_results_require_a_house_linked_event(): void
    {
        $admin = $this->createActivityUser('activities-result-service-admin-two@example.com', ['Activities Admin']);
        $assistant = $this->createActivityUser('activities-result-service-assistant-two@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 2);
        $house = $this->createHouseForTerm($term, $admin, $assistant, 'Batawana');
        $activity = $this->createActivityRecord($term, $admin, [
            'status' => Activity::STATUS_ACTIVE,
            'allow_house_linkage' => true,
        ]);
        $event = $this->createActivityEventRecord($activity, $admin, [
            'status' => ActivityEvent::STATUS_COMPLETED,
            'house_linked' => false,
        ]);

        $this->expectException(ValidationException::class);

        app(ActivityResultService::class)->syncResults($activity, $event, [
            'scope' => ActivityResult::PARTICIPANT_HOUSE,
            'results' => [
                $house->id => [
                    'selected' => true,
                    'result_label' => 'Champions',
                    'placement' => 1,
                    'points' => 9,
                ],
            ],
        ], $admin);
    }
}

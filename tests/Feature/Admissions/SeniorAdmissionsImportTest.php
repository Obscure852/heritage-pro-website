<?php

namespace Tests\Feature\Admissions;

use App\Imports\SeniorAdmissionsImport;
use App\Models\Admission;
use App\Models\AdmissionAcademic;
use App\Models\Grade;
use App\Models\Klass;
use App\Models\Role;
use App\Models\SchoolSetup;
use App\Models\SeniorAdmissionAcademic;
use App\Models\SeniorAdmissionPlacementCriteria;
use App\Models\Sponsor;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use App\Services\SeniorAdmissionPlacementService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class SeniorAdmissionsImportTest extends TestCase
{
    protected function setUp(): void {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Session\Middleware\AuthenticateSession::class);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');
        Cache::flush();

        $this->createSchema();
    }

    public function test_admissions_settings_page_shows_term_labels(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);

        $response = $this->actingAs($admin)->get(route('admissions.settings'));

        $response->assertOk();
        $response->assertSee("Term {$term->term}, {$term->year}");
        $response->assertSee('Placement Criteria');
    }

    public function test_senior_import_feature_is_hidden_for_non_senior_schools(): void {
        $admin = $this->createAdminUser();
        foreach (['Junior', 'Primary'] as $schoolType) {
            SchoolSetup::query()->update(['type' => $schoolType]);

            $indexResponse = $this->actingAs($admin)->get(route('admissions.index'));
            $indexResponse->assertOk();
            $indexResponse->assertDontSee('Placement Recommendations');

            $settingsResponse = $this->actingAs($admin)->get(route('admissions.settings'));
            $settingsResponse->assertNotFound();

            $placementResponse = $this->actingAs($admin)->get(route('admissions.placement'));
            $placementResponse->assertNotFound();
        }
    }

    public function test_saving_placement_criteria_persists_three_school_rows(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);

        $response = $this->actingAs($admin)
            ->from(route('admissions.settings'))
            ->post(route('admissions.store-placement-criteria'), [
                'summary_term_id' => $term->id,
                'criteria' => [
                    'triple' => [
                        'science_best_grade' => 'A',
                        'science_worst_grade' => 'B',
                        'mathematics_best_grade' => 'A',
                        'mathematics_worst_grade' => 'B',
                        'target_count' => 24,
                        'is_active' => '1',
                    ],
                    'double' => [
                        'science_best_grade' => 'C',
                        'science_worst_grade' => 'D',
                        'mathematics_best_grade' => 'C',
                        'mathematics_worst_grade' => 'D',
                        'target_count' => 36,
                        'is_active' => '1',
                    ],
                    'single' => [
                        'science_best_grade' => '',
                        'science_worst_grade' => '',
                        'mathematics_best_grade' => '',
                        'mathematics_worst_grade' => '',
                        'target_count' => 18,
                        'is_active' => '1',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admissions.settings', ['summary_term_id' => $term->id]));
        $response->assertSessionHas('message');
        $this->assertSame(3, SeniorAdmissionPlacementCriteria::query()->count());
        $this->assertDatabaseHas('senior_admission_placement_criteria', [
            'pathway' => 'triple',
            'science_best_grade' => 'A',
            'science_worst_grade' => 'B',
            'mathematics_best_grade' => 'A',
            'mathematics_worst_grade' => 'B',
            'target_count' => 24,
        ]);
        $this->assertDatabaseHas('senior_admission_placement_criteria', [
            'pathway' => 'single',
            'target_count' => 18,
        ]);
    }

    public function test_placement_criteria_validation_rejects_reversed_grade_bands(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);

        $response = $this->actingAs($admin)
            ->from(route('admissions.settings'))
            ->post(route('admissions.store-placement-criteria'), [
                'summary_term_id' => $term->id,
                'criteria' => [
                    'triple' => [
                        'science_best_grade' => 'D',
                        'science_worst_grade' => 'B',
                        'mathematics_best_grade' => 'A',
                        'mathematics_worst_grade' => 'B',
                        'target_count' => 24,
                        'is_active' => '1',
                    ],
                    'double' => [
                        'science_best_grade' => 'C',
                        'science_worst_grade' => 'D',
                        'mathematics_best_grade' => 'C',
                        'mathematics_worst_grade' => 'D',
                        'target_count' => 36,
                        'is_active' => '1',
                    ],
                    'single' => [
                        'target_count' => 18,
                        'is_active' => '1',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admissions.settings', ['summary_term_id' => $term->id]));
        $response->assertSessionHasErrors(['criteria.triple.science_best_grade']);
        $this->assertSame(0, SeniorAdmissionPlacementCriteria::query()->count());
    }

    public function test_valid_workbook_import_creates_admission_and_senior_academic_record(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(987765);

        $response = $this->actingAs($admin)
            ->from(route('admissions.settings'))
            ->post(route('admissions.import-senior'), [
            'term_id' => $term->id,
            'file' => $this->makeSeniorAdmissionsCsv([
                $this->baseRow([
                    'connect_id' => '987765',
                    'firstname' => 'AARON',
                    'lastname' => 'ANDILE KGOSI',
                    'gender' => 'M',
                    'nationality' => 'Motswana',
                    'date_of_birth' => '15/06/2009',
                    'status' => 'Current',
                    'grade' => 'D',
                    'grade_applying_for' => 'F4',
                    'english' => 'D',
                    'setswana' => 'E',
                    'science' => 'D',
                    'mathematics' => 'E',
                    'social_studies' => 'D',
                ]),
            ]),
            ]);

        $response->assertRedirect(route('admissions.settings'));
        $response->assertSessionHas('message');

        $admission = Admission::query()->firstOrFail();
        $this->assertSame($sponsor->id, $admission->sponsor_id);
        $this->assertSame('987765', $admission->connect_id);
        $this->assertSame($term->id, $admission->term_id);
        $this->assertSame('F4', $admission->grade_applying_for);
        $this->assertSame('Current', $admission->status);
        $this->assertSame('Motswana', $admission->nationality);
        $this->assertStringStartsWith('F4IMP-987765-', $admission->id_number);

        $academic = SeniorAdmissionAcademic::query()->firstOrFail();
        $this->assertSame($admission->id, $academic->admission_id);
        $this->assertSame('D', $academic->overall);
        $this->assertSame('D', $academic->english);
        $this->assertSame('E', $academic->setswana);
        $this->assertSame('D', $academic->science);
    }

    public function test_delete_before_import_clears_only_the_selected_term(): void {
        $admin = $this->createAdminUser();
        $selectedTerm = $this->currentYearTerm(1);
        $otherTerm = $this->currentYearTerm(2);
        $selectedSponsor = $this->createSponsor(111111);
        $otherSponsor = $this->createSponsor(222222);

        $selectedAdmission = $this->createAdmission($selectedTerm, $selectedSponsor, 'Old Selected');
        SeniorAdmissionAcademic::create([
            'admission_id' => $selectedAdmission->id,
            'overall' => 'C',
        ]);

        $otherAdmission = $this->createAdmission($otherTerm, $otherSponsor, 'Other Term');

        $response = $this->actingAs($admin)
            ->from(route('admissions.settings'))
            ->post(route('admissions.import-senior'), [
            'term_id' => $selectedTerm->id,
            'delete_existing_term_admissions' => '1',
            'file' => $this->makeSeniorAdmissionsCsv([
                $this->baseRow([
                    'connect_id' => '111111',
                    'firstname' => 'NEW',
                    'lastname' => 'STUDENT',
                    'gender' => 'F',
                    'nationality' => 'Motswana',
                    'date_of_birth' => '12/03/2010',
                    'status' => 'Current',
                    'grade' => 'B',
                    'grade_applying_for' => 'F4',
                    'english' => 'B',
                ]),
            ]),
            ]);

        $response->assertRedirect(route('admissions.settings'));

        $this->assertCount(1, Admission::query()->where('term_id', $selectedTerm->id)->get());
        $this->assertCount(1, Admission::query()->where('term_id', $otherTerm->id)->get());
        $this->assertDatabaseMissing('admissions', ['id' => $selectedAdmission->id]);
        $this->assertDatabaseHas('admissions', ['id' => $otherAdmission->id]);
    }

    public function test_missing_sponsor_record_does_not_block_import_and_connect_id_is_stored(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);

        $response = $this->actingAs($admin)
            ->from(route('admissions.settings'))
            ->post(route('admissions.import-senior'), [
            'term_id' => $term->id,
            'file' => $this->makeSeniorAdmissionsCsv([
                $this->baseRow([
                    'connect_id' => '999999',
                    'firstname' => 'UNKNOWN',
                    'lastname' => 'SPONSOR',
                    'gender' => 'M',
                    'nationality' => 'Motswana',
                    'date_of_birth' => '10/02/2010',
                    'status' => 'Current',
                    'grade' => 'C',
                    'grade_applying_for' => 'F4',
                ]),
            ]),
            ]);

        $response->assertRedirect(route('admissions.settings'));
        $response->assertSessionHas('message');

        $admission = Admission::query()->firstOrFail();
        $this->assertNull($admission->sponsor_id);
        $this->assertSame('999999', $admission->connect_id);
        $this->assertSame('Unknown', $admission->first_name);
        $this->assertSame('Sponsor', $admission->last_name);
    }

    public function test_non_f4_rows_are_rejected(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $this->createSponsor(333333);

        $response = $this->actingAs($admin)
            ->from(route('admissions.settings'))
            ->post(route('admissions.import-senior'), [
            'term_id' => $term->id,
            'file' => $this->makeSeniorAdmissionsCsv([
                $this->baseRow([
                    'connect_id' => '333333',
                    'firstname' => 'BAD',
                    'lastname' => 'GRADE',
                    'gender' => 'F',
                    'nationality' => 'Motswana',
                    'date_of_birth' => '10/02/2010',
                    'status' => 'Current',
                    'grade' => 'C',
                    'grade_applying_for' => 'F5',
                ]),
            ]),
            ]);

        $response->assertRedirect(route('admissions.settings'));
        $response->assertSessionHasErrors();
        $this->assertSame(0, Admission::query()->count());
    }

    public function test_duplicate_row_for_same_term_is_skipped(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $this->createSponsor(444444);
        $file = $this->makeSeniorAdmissionsCsv([
            $this->baseRow([
                'connect_id' => '444444',
                'firstname' => 'DUPLICATE',
                'lastname' => 'STUDENT',
                'gender' => 'M',
                'nationality' => 'Motswana',
                'date_of_birth' => '11/02/2010',
                'status' => 'Current',
                'grade' => 'B',
                'grade_applying_for' => 'F4',
            ]),
        ]);

        $this->actingAs($admin)
            ->from(route('admissions.settings'))
            ->post(route('admissions.import-senior'), [
            'term_id' => $term->id,
            'file' => $file,
            ]);

        $response = $this->actingAs($admin)
            ->from(route('admissions.settings'))
            ->post(route('admissions.import-senior'), [
            'term_id' => $term->id,
            'file' => $this->makeSeniorAdmissionsCsv([
                $this->baseRow([
                    'connect_id' => '444444',
                    'firstname' => 'DUPLICATE',
                    'lastname' => 'STUDENT',
                    'gender' => 'M',
                    'nationality' => 'Motswana',
                    'date_of_birth' => '11/02/2010',
                    'status' => 'Current',
                    'grade' => 'B',
                    'grade_applying_for' => 'F4',
                ]),
            ]),
            ]);

        $response->assertRedirect(route('admissions.settings'));
        $response->assertSessionHas('warning');
        $this->assertSame(1, Admission::query()->count());
    }

    public function test_junior_school_admission_view_shows_legacy_academic_form(): void {
        $admin = $this->createAdminUser();
        SchoolSetup::query()->update(['type' => 'Junior']);
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(555550);
        $this->createActiveGrade($term, 'F4', 'Junior');
        $admission = $this->createAdmission($term, $sponsor, 'JUNIOR');

        $response = $this->actingAs($admin)->get(route('admissions.admissions-view', $admission->id));

        $response->assertOk();
        $response->assertSee('Academic Information');
        $response->assertSee('Science');
        $response->assertSee('Mathematics');
        $response->assertSee('English');
        $response->assertDontSee('Design & Technology');
        $response->assertDontSee('Private Agriculture');
        $response->assertDontSee('Save Academic Grades');
        $response->assertDontSee('F3 Junior Results');
    }

    public function test_primary_school_admission_view_shows_legacy_academic_form(): void {
        $admin = $this->createAdminUser();
        SchoolSetup::query()->update(['type' => 'Primary']);
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(555551);
        $this->createActiveGrade($term, 'F4', 'Primary');
        $admission = $this->createAdmission($term, $sponsor, 'PRIMARY');

        $response = $this->actingAs($admin)->get(route('admissions.admissions-view', $admission->id));

        $response->assertOk();
        $response->assertSee('Academic Information');
        $response->assertSee('Science');
        $response->assertSee('Mathematics');
        $response->assertSee('English');
        $response->assertDontSee('Design & Technology');
        $response->assertDontSee('Private Agriculture');
        $response->assertDontSee('Save Academic Grades');
        $response->assertDontSee('F3 Junior Results');
    }

    public function test_non_senior_admission_view_only_shows_enroll_now_for_offer_accepted_status(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);

        foreach ([
            ['type' => 'Junior', 'grade_name' => 'F1', 'level' => 'Junior', 'connect_id' => 555560, 'prefix' => 'JUN'],
            ['type' => 'Primary', 'grade_name' => 'STD 1', 'level' => 'Primary', 'connect_id' => 555561, 'prefix' => 'PRI'],
        ] as $scenario) {
            SchoolSetup::query()->update(['type' => $scenario['type']]);
            $sponsor = $this->createSponsor($scenario['connect_id']);
            $this->createActiveGrade($term, $scenario['grade_name'], $scenario['level']);

            $offerAccepted = $this->createAdmission($term, $sponsor, $scenario['prefix'] . 'OFFER');
            $offerAccepted->update([
                'grade_applying_for' => $scenario['grade_name'],
                'status' => 'Offer Accepted',
            ]);

            $currentAdmission = $this->createAdmission($term, $sponsor, $scenario['prefix'] . 'CURRENT');
            $currentAdmission->update([
                'grade_applying_for' => $scenario['grade_name'],
                'status' => 'Current',
            ]);

            $offerResponse = $this->actingAs($admin)->get(route('admissions.admissions-view', $offerAccepted->id));
            $offerResponse->assertOk();
            $offerResponse->assertSee('Enroll Now');
            $offerResponse->assertDontSee('Recommended Science Pathway');

            $currentResponse = $this->actingAs($admin)->get(route('admissions.admissions-view', $currentAdmission->id));
            $currentResponse->assertOk();
            $currentResponse->assertDontSee('Enroll Now');
            $currentResponse->assertDontSee('Recommended Science Pathway');
        }
    }

    public function test_senior_school_admission_view_shows_jce_style_academic_grid(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(555555);
        $grade = $this->createActiveGrade($term);

        $admission = Admission::create([
            'sponsor_id' => $sponsor->id,
            'first_name' => 'VIEW',
            'last_name' => 'STUDENT',
            'middle_name' => null,
            'gender' => 'M',
            'date_of_birth' => '2010-02-10',
            'nationality' => 'Motswana',
            'phone' => null,
            'id_number' => 'F4IMP-555555-TESTHASH',
            'term_id' => $term->id,
            'grade_applying_for' => $grade->name,
            'year' => $term->year,
            'application_date' => now()->toDateString(),
            'status' => 'Current',
            'last_updated_by' => $admin->id,
        ]);

        SeniorAdmissionAcademic::create([
            'admission_id' => $admission->id,
            'overall' => 'B',
            'setswana' => 'C',
            'science' => 'A',
        ]);

        $response = $this->actingAs($admin)->get(route('admissions.admissions-view', $admission->id));

        $response->assertOk();
        $response->assertSee('Academic Information');
        $response->assertSee('Design & Technology');
        $response->assertSee('Private Agriculture');
        $response->assertSee('Save Academic Grades');
        $response->assertSee('Setswana');
        $response->assertSee('Science');
        $response->assertSee('B');
        $response->assertSee('C');
        $response->assertSee('A');
        $response->assertDontSee('F3 Junior Results');
    }

    public function test_admission_details_render_when_imported_admission_has_no_sponsor_yet(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $this->createActiveGrade($term);

        $admission = Admission::create([
            'sponsor_id' => null,
            'connect_id' => '777777',
            'first_name' => 'NO',
            'last_name' => 'SPONSOR',
            'middle_name' => null,
            'gender' => 'F',
            'date_of_birth' => '2010-02-10',
            'nationality' => 'Motswana',
            'phone' => null,
            'id_number' => 'F4IMP-777777-TESTHASH',
            'term_id' => $term->id,
            'grade_applying_for' => 'F4',
            'year' => $term->year,
            'application_date' => now()->toDateString(),
            'status' => 'Current',
            'last_updated_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admissions.admissions-view', $admission->id));

        $response->assertOk();
        $response->assertSee('Connect ID: 777777');
        $response->assertSee('Select Parent/Sponsor');
    }

    public function test_senior_academic_save_creates_new_record(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(600001);
        $this->createActiveGrade($term);
        $admission = $this->createAdmission($term, $sponsor, 'CREATE');

        $response = $this->actingAs($admin)
            ->from(route('admissions.admissions-view', $admission->id))
            ->post(route('admissions.create-senior-admission-academics'), [
                'admission_id' => $admission->id,
                'overall' => 'B',
                'science' => 'A',
                'english' => 'C',
                'private_agriculture' => 'D',
            ]);

        $response->assertRedirect(route('admissions.admissions-view', $admission->id));
        $response->assertSessionHas('message');
        $this->assertDatabaseHas('senior_admission_academics', [
            'admission_id' => $admission->id,
            'overall' => 'B',
            'science' => 'A',
            'english' => 'C',
            'private_agriculture' => 'D',
        ]);
    }

    public function test_senior_academic_save_updates_existing_record(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(600002);
        $this->createActiveGrade($term);
        $admission = $this->createAdmission($term, $sponsor, 'UPDATE');

        SeniorAdmissionAcademic::create([
            'admission_id' => $admission->id,
            'overall' => 'D',
            'science' => 'D',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admissions.admissions-view', $admission->id))
            ->post(route('admissions.create-senior-admission-academics'), [
                'admission_id' => $admission->id,
                'overall' => 'A',
                'science' => 'B',
                'english' => 'C',
            ]);

        $response->assertRedirect(route('admissions.admissions-view', $admission->id));
        $this->assertDatabaseHas('senior_admission_academics', [
            'admission_id' => $admission->id,
            'overall' => 'A',
            'science' => 'B',
            'english' => 'C',
        ]);
    }

    public function test_senior_academic_save_rejects_invalid_grades(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(600003);
        $this->createActiveGrade($term);
        $admission = $this->createAdmission($term, $sponsor, 'INVALID');

        $response = $this->actingAs($admin)
            ->from(route('admissions.admissions-view', $admission->id))
            ->post(route('admissions.create-senior-admission-academics'), [
                'admission_id' => $admission->id,
                'overall' => 'U',
                'science' => 'F',
            ]);

        $response->assertRedirect(route('admissions.admissions-view', $admission->id));
        $response->assertSessionHasErrors(['overall', 'science']);
        $this->assertSame(0, SeniorAdmissionAcademic::query()->count());
    }

    public function test_legacy_academic_save_route_still_updates_junior_and_primary_form(): void {
        $admin = $this->createAdminUser();
        SchoolSetup::query()->update(['type' => 'Junior']);
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(600004);
        $this->createActiveGrade($term, 'F4', 'Junior');
        $admission = $this->createAdmission($term, $sponsor, 'LEGACY');

        $response = $this->actingAs($admin)
            ->from(route('admissions.admissions-view', $admission->id))
            ->post(route('admissions.create-admission-academics'), [
                'admission_id' => $admission->id,
                'science' => 'B',
                'mathematics' => 'C',
                'english' => 'A',
            ]);

        $response->assertRedirect(route('admissions.admissions-view', $admission->id));
        $this->assertDatabaseHas('admission_academics', [
            'admission_id' => $admission->id,
            'science' => 'B',
            'mathematics' => 'C',
            'english' => 'A',
        ]);
        $this->assertSame(0, SeniorAdmissionAcademic::query()->count());
        $this->assertSame(1, AdmissionAcademic::query()->count());
    }

    public function test_non_senior_enrollment_route_still_creates_student_and_class_membership_records(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);

        foreach ([
            ['type' => 'Junior', 'grade_name' => 'F1', 'level' => 'Junior', 'connect_id' => 600005, 'prefix' => 'JUNENR'],
            ['type' => 'Primary', 'grade_name' => 'STD 1', 'level' => 'Primary', 'connect_id' => 600006, 'prefix' => 'PRIENR'],
        ] as $scenario) {
            SchoolSetup::query()->update(['type' => $scenario['type']]);
            $sponsor = $this->createSponsor($scenario['connect_id']);
            $grade = $this->createActiveGrade($term, $scenario['grade_name'], $scenario['level']);
            $klass = $this->createKlass($term, $grade, $scenario['grade_name'] . ' A ' . $scenario['type'], Klass::TYPE_DOUBLE_AWARD);

            $admission = $this->createAdmission($term, $sponsor, $scenario['prefix']);
            $admission->update([
                'grade_applying_for' => $scenario['grade_name'],
                'status' => 'Offer Accepted',
            ]);

            $response = $this->actingAs($admin)->post(route('admissions.enrol-admission', $admission->id), [
                'klass_id' => $klass->id,
            ]);

            $response->assertRedirect(route('admissions.index'));
            $response->assertSessionHas('message', 'Student enrolled successfully!');
            $this->assertDatabaseHas('admissions', [
                'id' => $admission->id,
                'status' => 'Enrolled',
            ]);

            $studentId = Student::query()
                ->where('first_name', $admission->first_name)
                ->where('last_name', $admission->last_name)
                ->value('id');

            $this->assertNotNull($studentId);
            $this->assertDatabaseHas('student_term', [
                'student_id' => $studentId,
                'term_id' => $klass->term_id,
                'grade_id' => $klass->grade_id,
                'year' => $klass->year,
                'status' => 'Current',
            ]);
            $this->assertDatabaseHas('klass_student', [
                'klass_id' => $klass->id,
                'student_id' => $studentId,
                'term_id' => $klass->term_id,
                'grade_id' => $klass->grade_id,
                'year' => $klass->year,
                'active' => 1,
            ]);
        }
    }

    public function test_default_rules_recommend_expected_pathways(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(610001);
        $this->createActiveGrade($term);

        $tripleAdmission = $this->createAdmission($term, $sponsor, 'TRIPLE');
        SeniorAdmissionAcademic::create([
            'admission_id' => $tripleAdmission->id,
            'science' => 'A',
            'mathematics' => 'B',
        ]);

        $doubleAdmission = $this->createAdmission($term, $sponsor, 'DOUBLE');
        SeniorAdmissionAcademic::create([
            'admission_id' => $doubleAdmission->id,
            'science' => 'C',
            'mathematics' => 'D',
        ]);

        $singleAdmission = $this->createAdmission($term, $sponsor, 'SINGLE');
        SeniorAdmissionAcademic::create([
            'admission_id' => $singleAdmission->id,
            'science' => 'E',
            'mathematics' => 'U',
        ]);

        $service = app(SeniorAdmissionPlacementService::class);

        $this->assertSame('Triple Science', $service->recommendForAdmission($tripleAdmission)['label']);
        $this->assertSame('Double Science', $service->recommendForAdmission($doubleAdmission)['label']);
        $this->assertSame('Single Science Award', $service->recommendForAdmission($singleAdmission)['label']);
    }

    public function test_missing_science_or_mathematics_grade_returns_unclassified(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(610002);
        $this->createActiveGrade($term);

        $admission = $this->createAdmission($term, $sponsor, 'MISSING');
        SeniorAdmissionAcademic::create([
            'admission_id' => $admission->id,
            'science' => null,
            'mathematics' => 'A',
        ]);

        $service = app(SeniorAdmissionPlacementService::class);

        $this->assertSame('Unclassified', $service->recommendForAdmission($admission)['label']);
    }

    public function test_changing_criteria_immediately_updates_recommendation_without_reimport(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(610003);
        $this->createActiveGrade($term);

        $admission = $this->createAdmission($term, $sponsor, 'CRITERIA');
        SeniorAdmissionAcademic::create([
            'admission_id' => $admission->id,
            'science' => 'B',
            'mathematics' => 'B',
        ]);

        $service = app(SeniorAdmissionPlacementService::class);
        $this->assertSame('Triple Science', $service->recommendForAdmission($admission)['label']);

        $this->actingAs($admin)
            ->post(route('admissions.store-placement-criteria'), [
                'summary_term_id' => $term->id,
                'criteria' => [
                    'triple' => [
                        'science_best_grade' => 'A',
                        'science_worst_grade' => 'A',
                        'mathematics_best_grade' => 'A',
                        'mathematics_worst_grade' => 'A',
                        'target_count' => 10,
                        'is_active' => '1',
                    ],
                    'double' => [
                        'science_best_grade' => 'B',
                        'science_worst_grade' => 'C',
                        'mathematics_best_grade' => 'B',
                        'mathematics_worst_grade' => 'C',
                        'target_count' => 20,
                        'is_active' => '1',
                    ],
                    'single' => [
                        'target_count' => 30,
                        'is_active' => '1',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertSame('Double Science', $service->recommendForAdmission($admission->fresh('seniorAdmissionAcademic'))['label']);
    }

    public function test_senior_enrollment_modal_prioritizes_matching_classes_and_keeps_show_all_toggle(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(610004);
        $grade = $this->createActiveGrade($term);
        $admission = $this->createAdmission($term, $sponsor, 'ENROLL');

        SeniorAdmissionAcademic::create([
            'admission_id' => $admission->id,
            'science' => 'A',
            'mathematics' => 'A',
        ]);

        $this->createKlass($term, $grade, 'F4 Triple A', 'Triple Award');
        $this->createKlass($term, $grade, 'F4 Double B', 'Double Award');
        $this->createKlass($term, $grade, 'F4 Single C', 'Single Award');

        $response = $this->actingAs($admin)->get(route('admissions.admissions-view', $admission->id));

        $response->assertOk();
        $response->assertSee('Recommended Science Pathway');
        $response->assertSee('Triple Science');
        $response->assertSee('Preferred class type: Triple Award');
        $response->assertSee('Show all classes');
        $response->assertSee('Matching classes are shown first');
    }

    public function test_target_summary_uses_current_recommendations_without_enforcing_caps(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(610005);
        $this->createActiveGrade($term);

        foreach (['SUMMARY1', 'SUMMARY2'] as $name) {
            $admission = $this->createAdmission($term, $sponsor, $name);
            SeniorAdmissionAcademic::create([
                'admission_id' => $admission->id,
                'science' => 'A',
                'mathematics' => 'A',
            ]);
        }

        $this->actingAs($admin)
            ->post(route('admissions.store-placement-criteria'), [
                'summary_term_id' => $term->id,
                'criteria' => [
                    'triple' => [
                        'science_best_grade' => 'A',
                        'science_worst_grade' => 'B',
                        'mathematics_best_grade' => 'A',
                        'mathematics_worst_grade' => 'B',
                        'target_count' => 1,
                        'is_active' => '1',
                    ],
                    'double' => [
                        'science_best_grade' => 'C',
                        'science_worst_grade' => 'D',
                        'mathematics_best_grade' => 'C',
                        'mathematics_worst_grade' => 'D',
                        'target_count' => 2,
                        'is_active' => '1',
                    ],
                    'single' => [
                        'target_count' => 3,
                        'is_active' => '1',
                    ],
                ],
            ])
            ->assertRedirect();

        $response = $this->actingAs($admin)->get(route('admissions.settings', ['summary_term_id' => $term->id]));

        $response->assertOk();
        $response->assertSee('Target Summary');
        $response->assertSee('Triple Science');

        $summary = collect($response->viewData('placementSummary'));
        $tripleSummary = $summary->firstWhere('pathway', 'triple');

        $this->assertSame(1, $tripleSummary['target_count']);
        $this->assertSame(2, $tripleSummary['current_count']);
        $this->assertSame(-1, $tripleSummary['difference']);
    }

    public function test_admissions_index_links_to_placement_recommendations_for_senior_schools(): void {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('admissions.index'));

        $response->assertOk();
        $response->assertSee('Placement Recommendations');
        $response->assertDontSee('Import F4');
    }

    public function test_placement_page_groups_students_and_uses_overall_grade_as_tie_breaker(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(620001);
        $this->createActiveGrade($term);
        $this->persistPlacementTargets(['triple' => 1, 'double' => 0, 'single' => 0]);

        $higherOverall = $this->createAdmission($term, $sponsor, 'ALPHA');
        SeniorAdmissionAcademic::create([
            'admission_id' => $higherOverall->id,
            'science' => 'A',
            'mathematics' => 'A',
            'overall' => 'A',
        ]);

        $lowerOverall = $this->createAdmission($term, $sponsor, 'BETA');
        SeniorAdmissionAcademic::create([
            'admission_id' => $lowerOverall->id,
            'science' => 'A',
            'mathematics' => 'A',
            'overall' => 'C',
        ]);

        $double = $this->createAdmission($term, $sponsor, 'GAMMA');
        SeniorAdmissionAcademic::create([
            'admission_id' => $double->id,
            'science' => 'C',
            'mathematics' => 'D',
            'overall' => 'B',
        ]);

        $single = $this->createAdmission($term, $sponsor, 'DELTA');
        SeniorAdmissionAcademic::create([
            'admission_id' => $single->id,
            'science' => 'E',
            'mathematics' => 'U',
            'overall' => 'D',
        ]);

        $unclassified = $this->createAdmission($term, $sponsor, 'EPSILON');
        SeniorAdmissionAcademic::create([
            'admission_id' => $unclassified->id,
            'science' => null,
            'mathematics' => 'A',
            'overall' => 'A',
        ]);

        $response = $this->actingAs($admin)->get(route('admissions.placement', ['term_id' => $term->id]));

        $response->assertOk();
        $response->assertSee('Triple Science');
        $response->assertSee('Double Science');
        $response->assertSee('Single Science Award');
        $response->assertSee('Unclassified');

        $groups = collect($response->viewData('placementGroups'));
        $tripleGroup = $groups->firstWhere('pathway', 'triple');
        $doubleGroup = $groups->firstWhere('pathway', 'double');
        $singleGroup = $groups->firstWhere('pathway', 'single');
        $unclassifiedGroup = $groups->firstWhere('pathway', 'unclassified');

        $this->assertSame(['ALPHA', 'BETA'], collect($tripleGroup['students'])->pluck('admission.first_name')->all());
        $this->assertTrue((bool) $tripleGroup['students'][0]['auto_selected']);
        $this->assertFalse((bool) $tripleGroup['students'][1]['auto_selected']);
        $this->assertSame(1, $tripleGroup['selected_count']);
        $this->assertSame(1, $doubleGroup['count']);
        $this->assertSame(1, $singleGroup['count']);
        $this->assertSame(1, $unclassifiedGroup['count']);
    }

    public function test_placement_page_renders_toggle_labels_and_default_selection_markers(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(620011);
        $grade = $this->createActiveGrade($term);
        $this->persistPlacementTargets(['triple' => 1, 'double' => 0, 'single' => 0]);

        $tripleRecommended = $this->createAdmission($term, $sponsor, 'TRIPA');
        SeniorAdmissionAcademic::create([
            'admission_id' => $tripleRecommended->id,
            'science' => 'A',
            'mathematics' => 'A',
            'overall' => 'A',
        ]);

        $tripleOverflow = $this->createAdmission($term, $sponsor, 'TRIPB');
        SeniorAdmissionAcademic::create([
            'admission_id' => $tripleOverflow->id,
            'science' => 'A',
            'mathematics' => 'A',
            'overall' => 'C',
        ]);

        $doubleCandidate = $this->createAdmission($term, $sponsor, 'DOUBA');
        SeniorAdmissionAcademic::create([
            'admission_id' => $doubleCandidate->id,
            'science' => 'C',
            'mathematics' => 'C',
            'overall' => 'B',
        ]);

        $this->createKlass($term, $grade, 'F4 Triple Toggle', 'Triple Award');
        $this->createKlass($term, $grade, 'F4 Double Toggle', 'Double Award');

        $response = $this->actingAs($admin)->get(route('admissions.placement', ['term_id' => $term->id]));

        $response->assertOk();
        $response->assertSee('Clear Selection');
        $response->assertSee('Select All');
        $response->assertSee('data-default-selected="1"', false);
        $response->assertSee('data-default-selected="0"', false);
    }

    public function test_bulk_allocation_from_placement_page_enrolls_selected_admissions(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(620002);
        $grade = $this->createActiveGrade($term);
        $admission = $this->createAdmission($term, $sponsor, 'ALLOCATE');

        SeniorAdmissionAcademic::create([
            'admission_id' => $admission->id,
            'science' => 'A',
            'mathematics' => 'A',
            'overall' => 'B',
        ]);

        $klass = $this->createKlass($term, $grade, 'F4 Triple A', 'Triple Award');

        $response = $this->actingAs($admin)
            ->from(route('admissions.placement', ['term_id' => $term->id]))
            ->post(route('admissions.allocate-placement'), [
                'term_id' => $term->id,
                'pathway' => 'triple',
                'selected_admissions' => [$admission->id],
                'allocations' => [
                    $admission->id => ['klass_id' => $klass->id],
                ],
            ]);

        $response->assertRedirect(route('admissions.placement', ['term_id' => $term->id]));
        $response->assertSessionHas('message');
        $this->assertDatabaseHas('students', [
            'first_name' => 'ALLOCATE',
            'last_name' => 'Student',
            'status' => 'Current',
        ]);
        $this->assertDatabaseHas('student_term', [
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'status' => 'Current',
        ]);
        $this->assertDatabaseHas('klass_student', [
            'klass_id' => $klass->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
        ]);
        $this->assertDatabaseHas('admissions', [
            'id' => $admission->id,
            'status' => 'Enrolled',
        ]);
    }

    public function test_manual_class_override_allows_cross_type_class_when_matching_classes_exist(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(620003);
        $grade = $this->createActiveGrade($term);
        $admission = $this->createAdmission($term, $sponsor, 'MISMATCH');

        SeniorAdmissionAcademic::create([
            'admission_id' => $admission->id,
            'science' => 'A',
            'mathematics' => 'A',
            'overall' => 'A',
        ]);

        $this->createKlass($term, $grade, 'F4 Triple A', 'Triple Award');
        $wrongKlass = $this->createKlass($term, $grade, 'F4 Double B', 'Double Award');

        $response = $this->actingAs($admin)
            ->from(route('admissions.placement', ['term_id' => $term->id]))
            ->post(route('admissions.allocate-placement'), [
                'term_id' => $term->id,
                'pathway' => 'triple',
                'selected_admissions' => [$admission->id],
                'allocations' => [
                    $admission->id => ['klass_id' => $wrongKlass->id],
                ],
            ]);

        $response->assertRedirect(route('admissions.placement', ['term_id' => $term->id]));
        $response->assertSessionHas('message');
        $this->assertDatabaseHas('students', [
            'first_name' => 'MISMATCH',
            'last_name' => 'Student',
        ]);
        $this->assertDatabaseHas('admissions', [
            'id' => $admission->id,
            'status' => 'Enrolled',
        ]);
        $this->assertDatabaseHas('klass_student', [
            'klass_id' => $wrongKlass->id,
            'student_id' => Student::query()->where('first_name', 'MISMATCH')->value('id'),
        ]);
    }

    public function test_auto_allocation_with_pathway_only_processes_the_specified_pathway(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(630001);
        $grade = $this->createActiveGrade($term);

        $tripleAdmission = $this->createAdmission($term, $sponsor, 'TRIPLEONLY');
        SeniorAdmissionAcademic::create([
            'admission_id' => $tripleAdmission->id,
            'science' => 'A',
            'mathematics' => 'A',
            'overall' => 'A',
        ]);

        $doubleAdmission = $this->createAdmission($term, $sponsor, 'DOUBLEONLY');
        SeniorAdmissionAcademic::create([
            'admission_id' => $doubleAdmission->id,
            'science' => 'C',
            'mathematics' => 'D',
            'overall' => 'C',
        ]);

        $tripleKlass = $this->createKlass($term, $grade, 'F4 Triple X', 'Triple Award');
        $doubleKlass = $this->createKlass($term, $grade, 'F4 Double X', 'Double Award');

        $response = $this->actingAs($admin)
            ->from(route('admissions.placement', ['term_id' => $term->id]))
            ->post(route('admissions.allocate-placement'), [
                'term_id' => $term->id,
                'pathway' => 'triple',
                'selected_admissions' => [$tripleAdmission->id],
            ]);

        $response->assertRedirect(route('admissions.placement', ['term_id' => $term->id]));
        $response->assertSessionHas('message');

        $this->assertDatabaseHas('students', ['first_name' => 'TRIPLEONLY', 'status' => 'Current']);
        $this->assertDatabaseHas('admissions', ['id' => $tripleAdmission->id, 'status' => 'Enrolled']);

        $this->assertDatabaseMissing('students', ['first_name' => 'DOUBLEONLY']);
        $this->assertDatabaseHas('admissions', ['id' => $doubleAdmission->id, 'status' => 'Current']);
    }

    public function test_allocation_without_pathway_parameter_fails_validation(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(630002);
        $grade = $this->createActiveGrade($term);
        $admission = $this->createAdmission($term, $sponsor, 'NOPATHWAY');

        SeniorAdmissionAcademic::create([
            'admission_id' => $admission->id,
            'science' => 'A',
            'mathematics' => 'A',
            'overall' => 'B',
        ]);

        $this->createKlass($term, $grade, 'F4 Triple Z', 'Triple Award');

        $response = $this->actingAs($admin)
            ->from(route('admissions.placement', ['term_id' => $term->id]))
            ->post(route('admissions.allocate-placement'), [
                'term_id' => $term->id,
                'selected_admissions' => [$admission->id],
            ]);

        $response->assertRedirect(route('admissions.placement', ['term_id' => $term->id]));
        $response->assertSessionHasErrors(['pathway']);
        $this->assertDatabaseMissing('students', ['first_name' => 'NOPATHWAY']);
        $this->assertDatabaseHas('admissions', ['id' => $admission->id, 'status' => 'Current']);
    }

    public function test_mixed_allocation_supports_auto_assign_and_manual_override_in_one_submit(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(630003);
        $grade = $this->createActiveGrade($term);

        $manualAdmission = $this->createAdmission($term, $sponsor, 'MANUALMIX');
        SeniorAdmissionAcademic::create([
            'admission_id' => $manualAdmission->id,
            'science' => 'A',
            'mathematics' => 'A',
            'overall' => 'A',
        ]);

        $autoAdmission = $this->createAdmission($term, $sponsor, 'AUTOMIX');
        SeniorAdmissionAcademic::create([
            'admission_id' => $autoAdmission->id,
            'science' => 'A',
            'mathematics' => 'A',
            'overall' => 'B',
        ]);

        $tripleKlass = $this->createKlass($term, $grade, 'F4 Triple M', 'Triple Award');
        $doubleKlass = $this->createKlass($term, $grade, 'F4 Double M', 'Double Award');

        $response = $this->actingAs($admin)
            ->from(route('admissions.placement', ['term_id' => $term->id]))
            ->post(route('admissions.allocate-placement'), [
                'term_id' => $term->id,
                'pathway' => 'triple',
                'selected_admissions' => [$manualAdmission->id, $autoAdmission->id],
                'allocations' => [
                    $manualAdmission->id => ['klass_id' => $doubleKlass->id],
                ],
            ]);

        $response->assertRedirect(route('admissions.placement', ['term_id' => $term->id]));
        $response->assertSessionHas('message');

        $manualStudentId = Student::query()->where('first_name', 'MANUALMIX')->value('id');
        $autoStudentId = Student::query()->where('first_name', 'AUTOMIX')->value('id');

        $this->assertNotNull($manualStudentId);
        $this->assertNotNull($autoStudentId);
        $this->assertDatabaseHas('klass_student', ['klass_id' => $doubleKlass->id, 'student_id' => $manualStudentId]);
        $this->assertDatabaseHas('klass_student', ['klass_id' => $tripleKlass->id, 'student_id' => $autoStudentId]);
    }

    public function test_auto_allocation_warns_but_enrolls_when_matching_classes_are_full(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(630004);
        $grade = $this->createActiveGrade($term);
        $admission = $this->createAdmission($term, $sponsor, 'FULLAUTO');

        SeniorAdmissionAcademic::create([
            'admission_id' => $admission->id,
            'science' => 'A',
            'mathematics' => 'A',
            'overall' => 'A',
        ]);

        $this->createKlass($term, $grade, 'F4 Triple Full', 'Triple Award', 0);

        $response = $this->actingAs($admin)
            ->from(route('admissions.placement', ['term_id' => $term->id]))
            ->post(route('admissions.allocate-placement'), [
                'term_id' => $term->id,
                'pathway' => 'triple',
                'selected_admissions' => [$admission->id],
            ]);

        $response->assertRedirect(route('admissions.placement', ['term_id' => $term->id]));
        $response->assertSessionHas('message');
        $response->assertSessionHas('warning');
        $this->assertDatabaseHas('students', ['first_name' => 'FULLAUTO']);
        $this->assertDatabaseHas('admissions', ['id' => $admission->id, 'status' => 'Enrolled']);
    }

    public function test_manual_override_into_full_class_succeeds_with_warning(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(630005);
        $grade = $this->createActiveGrade($term);
        $admission = $this->createAdmission($term, $sponsor, 'FULLMANUAL');

        SeniorAdmissionAcademic::create([
            'admission_id' => $admission->id,
            'science' => 'A',
            'mathematics' => 'A',
            'overall' => 'A',
        ]);

        $fullKlass = $this->createKlass($term, $grade, 'F4 Double Full', 'Double Award', 0);

        $response = $this->actingAs($admin)
            ->from(route('admissions.placement', ['term_id' => $term->id]))
            ->post(route('admissions.allocate-placement'), [
                'term_id' => $term->id,
                'pathway' => 'triple',
                'selected_admissions' => [$admission->id],
                'allocations' => [
                    $admission->id => ['klass_id' => $fullKlass->id],
                ],
            ]);

        $response->assertRedirect(route('admissions.placement', ['term_id' => $term->id]));
        $response->assertSessionHas('message');
        $response->assertSessionHas('warning');
        $this->assertDatabaseHas('students', ['first_name' => 'FULLMANUAL']);
        $this->assertDatabaseHas('admissions', ['id' => $admission->id, 'status' => 'Enrolled']);
    }

    public function test_senior_admission_view_allows_current_status_enrollment_and_blocks_unclassified(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(630006);
        $grade = $this->createActiveGrade($term);

        $allocatable = $this->createAdmission($term, $sponsor, 'VIEWALLOC');
        SeniorAdmissionAcademic::create([
            'admission_id' => $allocatable->id,
            'science' => 'A',
            'mathematics' => 'A',
            'overall' => 'A',
        ]);

        $unclassified = $this->createAdmission($term, $sponsor, 'VIEWMISS');
        SeniorAdmissionAcademic::create([
            'admission_id' => $unclassified->id,
            'science' => null,
            'mathematics' => 'A',
            'overall' => 'A',
        ]);

        $this->createKlass($term, $grade, 'F4 Triple View', 'Triple Award');

        $allocatableResponse = $this->actingAs($admin)->get(route('admissions.admissions-view', $allocatable->id));
        $allocatableResponse->assertOk();
        $allocatableResponse->assertSee('Enroll Now');

        $unclassifiedResponse = $this->actingAs($admin)->get(route('admissions.admissions-view', $unclassified->id));
        $unclassifiedResponse->assertOk();
        $unclassifiedResponse->assertDontSee('Enroll Now');
    }

    public function test_single_pathway_is_hidden_when_single_award_classes_do_not_exist(): void {
        $admin = $this->createAdminUser();
        $term = $this->currentYearTerm(1);
        $sponsor = $this->createSponsor(630007);
        $grade = $this->createActiveGrade($term);

        $singleCandidate = $this->createAdmission($term, $sponsor, 'NOSINGLE');
        SeniorAdmissionAcademic::create([
            'admission_id' => $singleCandidate->id,
            'science' => 'E',
            'mathematics' => 'U',
            'overall' => 'D',
        ]);

        $this->createKlass($term, $grade, 'F4 Triple Hidden', 'Triple Award');
        $this->createKlass($term, $grade, 'F4 Double Hidden', 'Double Award');

        $response = $this->actingAs($admin)->get(route('admissions.placement', ['term_id' => $term->id]));

        $response->assertOk();
        $groups = collect($response->viewData('placementGroups'));
        $this->assertNull($groups->firstWhere('pathway', 'single'));
        $this->assertSame(1, $groups->firstWhere('pathway', 'double')['count']);
    }

    public function test_settings_guide_reflects_choose_class_and_advisory_capacity_rules(): void {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('admissions.settings'));

        $response->assertOk();
        $response->assertSee('Auto-Assign vs Choose Class');
        $response->assertSee('Choose Class');
        $response->assertSee('all matching classes are already full');
        $response->assertSee('Single Science category will not appear');
        $response->assertDontSee('Allocate button will be unavailable');
    }

    private function createAdminUser(): User {
        $user = User::withoutEvents(fn() => User::factory()->create([
            'status' => 'Current',
            'active' => true,
        ]));
        $adminRole = Role::query()->firstOrCreate(['name' => 'Administrator']);
        $user->roles()->attach($adminRole);

        return $user;
    }

    private function createSponsor(int $connectId): Sponsor {
        return Sponsor::query()->create([
            'connect_id' => $connectId,
            'title' => 'Mr',
            'first_name' => 'Parent',
            'last_name' => 'Record',
            'email' => "parent{$connectId}@example.com",
            'gender' => 'M',
            'date_of_birth' => '1980-01-01',
            'nationality' => 'Motswana',
            'relation' => 'Father',
            'status' => 'Current',
            'id_number' => 'SPONSOR-' . $connectId,
            'phone' => '71234567',
            'profession' => 'Teacher',
            'work_place' => 'School',
            'telephone' => '3901234',
            'password' => bcrypt('password'),
            'last_updated_by' => 'Administrator',
        ]);
    }

    private function currentYearTerm(int $termNumber): Term {
        return Term::query()
            ->where('year', (int) now()->format('Y'))
            ->where('term', $termNumber)
            ->firstOrFail();
    }

    private function createActiveGrade(Term $term, string $name = 'F4', string $level = 'Senior'): Grade {
        return Grade::create([
            'sequence' => 1,
            'name' => $name,
            'promotion' => 'F5',
            'description' => 'Form 4',
            'level' => $level,
            'active' => true,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);
    }

    private function createAdmission(Term $term, Sponsor $sponsor, string $firstName): Admission {
        return Admission::create([
            'sponsor_id' => $sponsor->id,
            'first_name' => $firstName,
            'last_name' => 'Student',
            'middle_name' => null,
            'gender' => 'M',
            'date_of_birth' => '2010-01-01',
            'nationality' => 'Motswana',
            'phone' => null,
            'id_number' => 'LEGACY-' . $firstName . '-' . $term->id,
            'term_id' => $term->id,
            'grade_applying_for' => 'F4',
            'year' => $term->year,
            'application_date' => now()->toDateString(),
            'status' => 'Current',
            'last_updated_by' => 1,
        ]);
    }

    private function createKlass(Term $term, Grade $grade, string $name, string $type, ?int $maxStudents = null): Klass {
        return Klass::create([
            'name' => $name,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'type' => $type,
            'max_students' => $maxStudents,
            'year' => $term->year,
        ]);
    }

    private function persistPlacementTargets(array $targets): void
    {
        $service = app(SeniorAdmissionPlacementService::class);
        $service->persistCriteria(SchoolSetup::current(), [
            'triple' => [
                'science_best_grade' => 'A',
                'science_worst_grade' => 'B',
                'mathematics_best_grade' => 'A',
                'mathematics_worst_grade' => 'B',
                'target_count' => $targets['triple'] ?? 0,
                'is_active' => true,
            ],
            'double' => [
                'science_best_grade' => 'C',
                'science_worst_grade' => 'D',
                'mathematics_best_grade' => 'C',
                'mathematics_worst_grade' => 'D',
                'target_count' => $targets['double'] ?? 0,
                'is_active' => true,
            ],
            'single' => [
                'target_count' => $targets['single'] ?? 0,
                'is_active' => true,
            ],
        ]);
    }

    private function makeSeniorAdmissionsCsv(array $rows): UploadedFile {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, SeniorAdmissionsImport::templateHeaders());

        foreach ($rows as $row) {
            fputcsv($handle, array_map(
                fn($header) => $row[$header] ?? '',
                SeniorAdmissionsImport::templateHeaders()
            ));
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        return UploadedFile::fake()->createWithContent('senior-admissions.csv', $contents);
    }

    private function baseRow(array $overrides = []): array {
        return array_merge(array_fill_keys(SeniorAdmissionsImport::templateHeaders(), ''), $overrides);
    }

    private function createSchema(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->string('email')->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->string('gender')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('position')->nullable();
            $table->string('area_of_work')->nullable();
            $table->string('nationality')->nullable();
            $table->string('phone')->nullable();
            $table->string('id_number')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->boolean('active')->default(true);
            $table->string('status')->nullable();
            $table->string('username')->nullable();
            $table->integer('year')->nullable();
            $table->string('last_updated_by')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('user_id');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('term');
            $table->integer('year');
            $table->boolean('closed')->default(false);
            $table->integer('extension_days')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sponsor_filter_id')->nullable();
            $table->bigInteger('connect_id');
            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable()->unique();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('relation')->nullable();
            $table->string('status')->nullable();
            $table->string('id_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('profession')->nullable();
            $table->string('work_place')->nullable();
            $table->string('telephone')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->string('last_updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sponsor_id')->nullable();
            $table->string('connect_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('gender');
            $table->date('date_of_birth');
            $table->string('nationality');
            $table->string('phone')->nullable();
            $table->string('id_number');
            $table->string('grade_applying_for');
            $table->date('application_date');
            $table->string('status')->default('Pending');
            $table->unsignedBigInteger('term_id');
            $table->integer('year');
            $table->integer('last_updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('connect_id')->nullable();
            $table->unsignedBigInteger('sponsor_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('id_number')->nullable();
            $table->string('password')->nullable();
            $table->string('status')->nullable();
            $table->integer('year')->nullable();
            $table->string('last_updated_by')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('student_term', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('grade_id');
            $table->integer('year')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('klass_student', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('klass_id');
            $table->unsignedBigInteger('student_id');
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('grade_id');
            $table->integer('year')->nullable();
            $table->timestamps();
        });

        Schema::create('admission_academics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id');
            $table->string('science')->nullable();
            $table->string('mathematics')->nullable();
            $table->string('english')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('admission_health_information', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id');
            $table->string('health_history')->nullable();
            $table->string('immunization_records')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('online_application_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id');
            $table->string('attachment_type')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        Schema::create('senior_admission_academics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id')->unique();
            $table->string('overall')->nullable();
            $table->string('english')->nullable();
            $table->string('setswana')->nullable();
            $table->string('science')->nullable();
            $table->string('mathematics')->nullable();
            $table->string('agriculture')->nullable();
            $table->string('social_studies')->nullable();
            $table->string('moral_education')->nullable();
            $table->string('design_and_technology')->nullable();
            $table->string('home_economics')->nullable();
            $table->string('office_procedures')->nullable();
            $table->string('accounting')->nullable();
            $table->string('french')->nullable();
            $table->string('art')->nullable();
            $table->string('music')->nullable();
            $table->string('physical_education')->nullable();
            $table->string('religious_education')->nullable();
            $table->string('private_agriculture')->nullable();
            $table->timestamps();
        });

        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->integer('sequence');
            $table->string('name');
            $table->string('promotion');
            $table->string('description');
            $table->string('level');
            $table->boolean('active');
            $table->unsignedBigInteger('term_id');
            $table->integer('year');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('klasses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedBigInteger('grade_id')->nullable();
            $table->string('type')->nullable();
            $table->unsignedInteger('max_students')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('year')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('senior_admission_placement_criteria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_setup_id');
            $table->string('pathway');
            $table->unsignedInteger('priority')->default(1);
            $table->string('science_best_grade')->nullable();
            $table->string('science_worst_grade')->nullable();
            $table->string('mathematics_best_grade')->nullable();
            $table->string('mathematics_worst_grade')->nullable();
            $table->string('science_ceiling_grade')->nullable();
            $table->string('promotion_pathway')->nullable();
            $table->unsignedInteger('target_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('nationalities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('school_setup', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->nullable();
            $table->string('school_id')->nullable();
            $table->string('type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('year')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('loggings', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->nullable();
            $table->string('location')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();
            $table->string('method')->nullable();
            $table->text('input')->nullable();
            $table->text('changes')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('s_m_s_api_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('category')->nullable();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->string('display_name')->nullable();
            $table->text('validation_rules')->nullable();
            $table->boolean('is_editable')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        $year = (int) now()->format('Y');
        foreach ([1, 2, 3] as $termNumber) {
            Term::query()->create([
                'start_date' => now()->startOfYear()->addMonths(($termNumber - 1) * 4)->toDateString(),
                'end_date' => now()->startOfYear()->addMonths(($termNumber * 4) - 1)->toDateString(),
                'term' => $termNumber,
                'year' => $year,
                'closed' => false,
                'extension_days' => 0,
            ]);
        }

        DB::table('school_setup')->insert([
            'school_name' => 'Test Senior School',
            'school_id' => 'TES-SENIOR-0001',
            'type' => 'Senior',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([
            'modules.leave_visible',
            'modules.staff_attendance_visible',
            'modules.staff_pdp_visible',
            'modules.welfare_visible',
            'modules.schemes_visible',
            'modules.communications_visible',
            'modules.lms_visible',
            'modules.assets_visible',
            'modules.fees_visible',
            'modules.library_visible',
            'modules.timetable_visible',
            'modules.invigilation_visible',
        ] as $key) {
            DB::table('s_m_s_api_settings')->insert([
                'key' => $key,
                'value' => '0',
                'category' => 'modules',
                'type' => 'boolean',
                'display_name' => $key,
                'is_editable' => true,
                'display_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

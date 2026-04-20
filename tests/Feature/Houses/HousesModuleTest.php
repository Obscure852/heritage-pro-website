<?php

namespace Tests\Feature\Houses;

use App\Models\Grade;
use App\Models\House;
use App\Models\Klass;
use App\Models\Role;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use App\Services\FinalsModuleRolloverService;
use App\Services\TermRolloverService;
use App\Services\YearRolloverReverseService;
use App\Services\YearRolloverService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class HousesModuleTest extends TestCase
{
    use EnsuresActivitiesPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        $this->ensureActivitiesPhaseOneSchema();
        $this->ensureUsersTable();
        $this->ensureHouseLifecycleTables();
        $this->ensureHouseColorColumns();
        $this->resetHouseModuleTables();

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

    public function test_house_store_normalizes_color_code_before_persisting(): void
    {
        $admin = $this->createUserWithRoles('houses-admin@example.com', ['Administrator']);
        $head = $this->createUserWithRoles('houses-head@example.com', ['Teacher']);
        $assistant = $this->createUserWithRoles('houses-assistant@example.com', ['Teacher']);
        $term = $this->createCurrentTerm();

        $response = $this->actingAs($admin)
            ->from(route('house.show'))
            ->post(route('house.store'), [
                'name' => 'Kgosi',
                'color_code' => '#ab12cd',
                'head' => $head->id,
                'assistant' => $assistant->id,
                'year' => $term->year,
            ]);

        $response->assertRedirect(route('house.show'));

        $this->assertDatabaseHas('houses', [
            'name' => 'Kgosi',
            'color_code' => '#AB12CD',
            'term_id' => $term->id,
        ]);
    }

    protected function ensureHouseColorColumns(): void
    {
        if (Schema::hasTable('houses') && !Schema::hasColumn('houses', 'color_code')) {
            Schema::table('houses', function (Blueprint $table): void {
                $table->string('color_code', 7)->default('#2563EB')->after('name');
            });
        }

        if (Schema::hasTable('final_houses') && !Schema::hasColumn('final_houses', 'color_code')) {
            Schema::table('final_houses', function (Blueprint $table): void {
                $table->string('color_code', 7)->nullable()->after('name');
            });
        }
    }

    protected function resetHouseModuleTables(): void
    {
        DB::table('user_house')->delete();
        DB::table('student_house')->delete();
        DB::table('final_houses')->delete();
        DB::table('rollover_histories')->delete();
        DB::table('term_rollover_histories')->delete();
        DB::table('klass_student')->delete();
        DB::table('student_term')->delete();
        DB::table('houses')->delete();
        DB::table('klasses')->delete();
        DB::table('grades')->delete();
        DB::table('students')->delete();
        DB::table('role_users')->delete();
        DB::table('roles')->delete();
        DB::table('users')->delete();
        DB::table('terms')->delete();
    }

    public function test_can_allocate_users_and_remove_them_via_single_and_bulk_actions(): void
    {
        $admin = $this->createUserWithRoles('houses-manager@example.com', ['Administrator']);
        $head = $this->createUserWithRoles('houses-user-head@example.com', ['Teacher']);
        $assistant = $this->createUserWithRoles('houses-user-assistant@example.com', ['Teacher']);
        $userOne = $this->createUserWithRoles('houses-user-one@example.com', ['Teacher'], ['position' => 'Teacher']);
        $userTwo = $this->createUserWithRoles('houses-user-two@example.com', ['Teacher'], ['position' => 'Teacher']);
        $userThree = $this->createUserWithRoles('houses-user-three@example.com', ['Teacher'], ['position' => 'Teacher']);
        $term = $this->createCurrentTerm();

        session(['selected_term_id' => $term->id]);

        $house = $this->createHouse($term, $head, $assistant, 'Kgosi', '#2563EB');
        $otherHouse = $this->createHouse($term, $assistant, $head, 'Tau', '#DC2626');

        DB::table('user_house')->insert([
            'user_id' => $userThree->id,
            'house_id' => $otherHouse->id,
            'term_id' => $term->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $allocateResponse = $this->actingAs($admin)
            ->from(route('house.open-house-users', ['id' => $house->id]))
            ->post(route('house.move-users', ['id' => $house->id]), [
                'users' => [$userOne->id, $userTwo->id, $userThree->id],
            ]);

        $allocateResponse->assertRedirect(route('house.open-house-users', ['id' => $house->id]));

        $this->assertDatabaseHas('user_house', [
            'user_id' => $userOne->id,
            'house_id' => $house->id,
            'term_id' => $term->id,
        ]);
        $this->assertDatabaseHas('user_house', [
            'user_id' => $userTwo->id,
            'house_id' => $house->id,
            'term_id' => $term->id,
        ]);
        $this->assertDatabaseHas('user_house', [
            'user_id' => $userThree->id,
            'house_id' => $otherHouse->id,
            'term_id' => $term->id,
        ]);

        $singleDeleteResponse = $this->actingAs($admin)
            ->from(route('house.house-view', ['houseId' => $house->id]))
            ->delete(route('house.delete-user', [$house->id, $userOne->id]));

        $singleDeleteResponse->assertRedirect(route('house.house-view', ['houseId' => $house->id]));
        $this->assertDatabaseMissing('user_house', [
            'user_id' => $userOne->id,
            'house_id' => $house->id,
            'term_id' => $term->id,
        ]);

        $bulkDeleteResponse = $this->actingAs($admin)
            ->from(route('house.house-view', ['houseId' => $house->id]))
            ->delete(route('house.delete-multiple-users', ['id' => $house->id]), [
                'users' => [$userTwo->id],
            ]);

        $bulkDeleteResponse->assertRedirect(route('house.house-view', ['houseId' => $house->id]));
        $this->assertDatabaseMissing('user_house', [
            'user_id' => $userTwo->id,
            'house_id' => $house->id,
            'term_id' => $term->id,
        ]);
    }

    public function test_house_detail_view_renders_student_and_user_sections_with_color_code(): void
    {
        $admin = $this->createUserWithRoles('houses-view-admin@example.com', ['Administrator']);
        $head = $this->createUserWithRoles('houses-view-head@example.com', ['Teacher']);
        $assistant = $this->createUserWithRoles('houses-view-assistant@example.com', ['Teacher']);
        $member = $this->createUserWithRoles('houses-view-member@example.com', ['Teacher'], [
            'position' => 'Teacher',
            'gender' => 'F',
        ]);
        $term = $this->createCurrentTerm();
        session(['selected_term_id' => $term->id]);

        $house = $this->createHouse($term, $head, $assistant, 'Kgosi', '#2563EB');
        $grade = $this->createGrade($term, 'F1');
        $klass = $this->createKlass($term, $grade, $admin, 'F1A');
        $student = $this->createStudent($term, $grade, $klass, ['first_name' => 'Aga', 'last_name' => 'Student']);

        DB::table('student_house')->insert([
            'student_id' => $student->id,
            'house_id' => $house->id,
            'term_id' => $term->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('user_house')->insert([
            'user_id' => $member->id,
            'house_id' => $house->id,
            'term_id' => $term->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('house.house-view', ['houseId' => $house->id]));

        $response->assertOk()
            ->assertSee('Student Members')
            ->assertSee('User Members')
            ->assertSee('Aga Student')
            ->assertSee($member->full_name)
            ->assertSee('#2563EB', false);
    }

    public function test_term_rollover_copies_house_color_and_user_memberships(): void
    {
        $head = $this->createUserWithRoles('term-rollover-head@example.com', ['Teacher']);
        $assistant = $this->createUserWithRoles('term-rollover-assistant@example.com', ['Teacher']);
        $member = $this->createUserWithRoles('term-rollover-member@example.com', ['Teacher']);
        $fromTerm = $this->createTerm(now()->subMonths(3)->startOfDay(), now()->subMonth()->endOfDay(), 1, 2026);
        $toTerm = $this->createTerm(now()->subDays(5)->startOfDay(), now()->addMonths(2)->endOfDay(), 2, 2026);

        $house = $this->createHouse($fromTerm, $head, $assistant, 'Kgosi', '#059669');

        DB::table('user_house')->insert([
            'user_id' => $member->id,
            'house_id' => $house->id,
            'term_id' => $fromTerm->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = new class extends TermRolloverService {
            public function rolloverHousesPublic(int $fromTermId, int $toTermId): array
            {
                return $this->rolloverHouses($fromTermId, $toTermId);
            }

            public function allocateUsersToNewHousesPublic(int $fromTermId, int $toTermId, array $mapping): array
            {
                return $this->allocateUsersToNewHouses($fromTermId, $toTermId, $mapping);
            }
        };

        $mapping = $service->rolloverHousesPublic($fromTerm->id, $toTerm->id);
        $service->allocateUsersToNewHousesPublic($fromTerm->id, $toTerm->id, $mapping);

        $newHouseId = $mapping[$house->id];

        $this->assertDatabaseHas('houses', [
            'id' => $newHouseId,
            'name' => 'Kgosi',
            'term_id' => $toTerm->id,
            'color_code' => '#059669',
        ]);

        $this->assertDatabaseHas('user_house', [
            'user_id' => $member->id,
            'house_id' => $newHouseId,
            'term_id' => $toTerm->id,
        ]);
    }

    public function test_year_rollover_copies_house_color_and_user_memberships(): void
    {
        $admin = $this->createUserWithRoles('year-rollover-admin@example.com', ['Administrator']);
        $head = $this->createUserWithRoles('year-rollover-head@example.com', ['Teacher']);
        $assistant = $this->createUserWithRoles('year-rollover-assistant@example.com', ['Teacher']);
        $member = $this->createUserWithRoles('year-rollover-member@example.com', ['Teacher']);
        $fromTerm = $this->createTerm(now()->subMonths(3)->startOfDay(), now()->subMonth()->endOfDay(), 3, 2026);
        $toTerm = $this->createTerm(now()->subDays(5)->startOfDay(), now()->addMonths(2)->endOfDay(), 1, 2027);

        $house = $this->createHouse($fromTerm, $head, $assistant, 'Kgosi', '#D97706');
        DB::table('user_house')->insert([
            'user_id' => $member->id,
            'house_id' => $house->id,
            'term_id' => $fromTerm->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = new class extends YearRolloverService {
            public function rolloverHousesPublic(Term $toTerm, Term $fromTerm): array
            {
                return $this->rolloverHouses($toTerm, $fromTerm);
            }

            public function rolloverUserHousesPublic(Term $toTerm, Term $fromTerm, array $mapping)
            {
                return $this->rolloverUserHouses($toTerm, $fromTerm, $mapping);
            }
        };

        $mapping = $service->rolloverHousesPublic($toTerm, $fromTerm);
        $service->rolloverUserHousesPublic($toTerm, $fromTerm, $mapping);

        $newHouseId = $mapping[$house->id];

        $this->assertDatabaseHas('houses', [
            'id' => $newHouseId,
            'name' => 'Kgosi',
            'term_id' => $toTerm->id,
            'color_code' => '#D97706',
        ]);

        $this->assertDatabaseHas('user_house', [
            'user_id' => $member->id,
            'house_id' => $newHouseId,
            'term_id' => $toTerm->id,
        ]);

        $this->assertDatabaseHas('user_house', [
            'user_id' => $member->id,
            'house_id' => $house->id,
            'term_id' => $fromTerm->id,
        ]);
    }

    public function test_year_rollover_reverse_removes_user_house_allocations_for_destination_term(): void
    {
        $head = $this->createUserWithRoles('year-reverse-head@example.com', ['Teacher']);
        $assistant = $this->createUserWithRoles('year-reverse-assistant@example.com', ['Teacher']);
        $member = $this->createUserWithRoles('year-reverse-member@example.com', ['Teacher']);
        $fromTerm = $this->createTerm(now()->subMonths(3)->startOfDay(), now()->subMonth()->endOfDay(), 3, 2026);
        $toTerm = $this->createTerm(now()->subDays(5)->startOfDay(), now()->addMonths(2)->endOfDay(), 1, 2027);

        $fromHouse = $this->createHouse($fromTerm, $head, $assistant, 'Kgosi', '#2563EB');
        $toHouse = $this->createHouse($toTerm, $assistant, $head, 'Kgosi', '#2563EB');
        $grade = $this->createGrade($toTerm, 'F1');
        $klass = $this->createKlass($toTerm, $grade, $head, 'F1A');
        $student = $this->createStudent($toTerm, $grade, $klass, [
            'first_name' => 'Reverse',
            'last_name' => 'Student',
        ]);

        DB::table('user_house')->insert([
            'user_id' => $member->id,
            'house_id' => $toHouse->id,
            'term_id' => $toTerm->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('student_house')->insert([
            'student_id' => $student->id,
            'house_id' => $toHouse->id,
            'term_id' => $toTerm->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $historyId = DB::table('rollover_histories')->insertGetId([
            'from_term_id' => $fromTerm->id,
            'to_term_id' => $toTerm->id,
            'status' => 'completed',
            'performed_by' => $head->id,
            'rollover_timestamp' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = new class extends YearRolloverReverseService {
            public function reverseStudentHousesPublic(int $historyId): void
            {
                $this->rolloverHistoryId = $historyId;
                $this->results = [];
                $this->reverseStudentHouses();
            }
        };

        $service->reverseStudentHousesPublic($historyId);

        $this->assertDatabaseMissing('user_house', [
            'user_id' => $member->id,
            'house_id' => $toHouse->id,
            'term_id' => $toTerm->id,
        ]);

        $this->assertDatabaseMissing('student_house', [
            'house_id' => $toHouse->id,
            'term_id' => $toTerm->id,
        ]);
    }

    public function test_finals_house_rollover_snapshots_color_code(): void
    {
        $head = $this->createUserWithRoles('finals-rollover-head@example.com', ['Teacher']);
        $assistant = $this->createUserWithRoles('finals-rollover-assistant@example.com', ['Teacher']);
        $fromTerm = $this->createTerm(now()->subMonths(2)->startOfDay(), now()->subWeeks(2)->endOfDay(), 3, 2026);
        $toTerm = $this->createTerm(now()->addDay()->startOfDay(), now()->addMonths(3)->endOfDay(), 1, 2027);

        $house = $this->createHouse($fromTerm, $head, $assistant, 'Kgosi', '#7C3AED');

        $service = new class extends FinalsModuleRolloverService {
            public function rolloverHousesPublic(Term $fromTerm, Term $toTerm): array
            {
                return $this->rolloverHouses($fromTerm, $toTerm);
            }
        };

        $mapping = $service->rolloverHousesPublic($fromTerm, $toTerm);

        $this->assertDatabaseHas('final_houses', [
            'id' => $mapping[$house->id],
            'original_house_id' => $house->id,
            'name' => 'Kgosi',
            'graduation_term_id' => $fromTerm->id,
            'graduation_year' => $toTerm->year,
            'color_code' => '#7C3AED',
        ]);
    }

    protected function ensureUsersTable(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->string('email')->unique();
            $table->string('gender')->nullable();
            $table->string('position')->nullable();
            $table->string('area_of_work')->nullable();
            $table->string('status')->default('Current');
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function ensureHouseLifecycleTables(): void
    {
        if (!Schema::hasTable('rollover_histories')) {
            Schema::create('rollover_histories', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('from_term_id');
                $table->unsignedBigInteger('to_term_id');
                $table->string('status')->default('in_progress');
                $table->unsignedBigInteger('performed_by')->nullable();
                $table->timestamp('rollover_timestamp')->nullable();
                $table->timestamp('reversed_timestamp')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('term_rollover_histories')) {
            Schema::create('term_rollover_histories', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('from_term_id');
                $table->unsignedBigInteger('to_term_id');
                $table->unsignedBigInteger('performed_by')->nullable();
                $table->json('mappings')->nullable();
                $table->string('status')->default('in-progress');
                $table->timestamp('reversed_at')->nullable();
                $table->unsignedBigInteger('reversed_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('final_houses')) {
            Schema::create('final_houses', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('original_house_id');
                $table->string('name');
                $table->string('color_code', 7)->nullable();
                $table->unsignedBigInteger('head');
                $table->unsignedBigInteger('assistant');
                $table->unsignedBigInteger('graduation_term_id');
                $table->unsignedSmallInteger('graduation_year');
                $table->timestamps();
                $table->softDeletes();
            });
        } elseif (!Schema::hasColumn('final_houses', 'color_code')) {
            Schema::table('final_houses', function (Blueprint $table): void {
                $table->string('color_code', 7)->nullable()->after('name');
            });
        }
    }

    protected function createUserWithRoles(string $email, array $roles, array $overrides = []): User
    {
        $user = User::withoutEvents(fn () => User::query()->create(array_merge([
            'firstname' => 'House',
            'lastname' => 'Tester',
            'email' => $email,
            'password' => 'secret',
            'status' => 'Current',
            'position' => 'Teacher',
            'area_of_work' => 'Teaching',
            'gender' => 'M',
            'year' => now()->year,
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

    protected function createCurrentTerm(): Term
    {
        return $this->createTerm(now()->subMonth()->startOfDay(), now()->addMonth()->endOfDay(), 1, now()->year);
    }

    protected function createTerm($startDate, $endDate, int $termNumber, int $year): Term
    {
        $payload = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'term' => $termNumber,
            'year' => $year,
            'closed' => false,
        ];

        if (Schema::hasColumn('terms', 'term_type')) {
            $payload['term_type'] = 'Academic';
        }

        return Term::query()->create($payload);
    }

    protected function createHouse(Term $term, User $head, User $assistant, string $name, string $colorCode): House
    {
        return House::query()->create([
            'name' => $name,
            'color_code' => $colorCode,
            'head' => $head->id,
            'assistant' => $assistant->id,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);
    }

    protected function createGrade(Term $term, string $name): Grade
    {
        return Grade::query()->create([
            'sequence' => 1,
            'name' => $name,
            'promotion' => 'Yes',
            'description' => $name . ' grade',
            'level' => 'Junior',
            'active' => true,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);
    }

    protected function createKlass(Term $term, Grade $grade, User $teacher, string $name): Klass
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

    protected function createStudent(Term $term, Grade $grade, Klass $klass, array $overrides = []): Student
    {
        $student = Student::withoutEvents(fn () => Student::query()->create(array_merge([
            'first_name' => 'House',
            'last_name' => 'Student',
            'status' => 'Current',
            'gender' => 'M',
            'date_of_birth' => '2012-01-01',
            'nationality' => 'Botswana',
            'id_number' => 'STU-' . strtoupper(substr(md5(uniqid((string) $term->id, true)), 0, 8)),
            'year' => $term->year,
            'password' => 'secret',
        ], $overrides)));

        DB::table('student_term')->insert([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => $term->year,
            'status' => 'Current',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

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

        return $student->fresh();
    }
}

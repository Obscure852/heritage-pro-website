<?php

namespace Tests\Feature\Staff;

use App\Models\SchoolSetup;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\EnsuresStaffProfileSchema;
use Tests\TestCase;

class StaffEarningBandSettingsTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresStaffProfileSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureStaffProfileSchema();

        SchoolSetup::firstOrCreate([
            'school_name' => 'Merementsi Junior Secondary School',
            'type' => 'Junior',
        ]);
    }

    public function test_staff_settings_page_shows_earning_bands_tab_and_seeded_defaults(): void
    {
        $admin = $this->createUser('staff-settings-admin@example.com');

        $this->withoutMiddleware()
            ->actingAs($admin)
            ->get(route('staff.staff-settings'))
            ->assertOk()
            ->assertSee('Earning Bands')
            ->assertSee('B4')
            ->assertSee('C4/3');
    }

    public function test_earning_bands_can_be_added_updated_and_deleted_from_settings(): void
    {
        $admin = $this->createUser('staff-settings-manage@example.com');

        $this->withoutMiddleware()
            ->actingAs($admin)
            ->post(route('staff.earning-bands.store'), [
                'band_name' => 'F1',
                'sort_order' => 30,
            ])
            ->assertRedirect();

        $bandId = DB::table('earning_bands')->where('name', 'F1')->value('id');

        $this->assertNotNull($bandId);

        $this->withoutMiddleware()
            ->actingAs($admin)
            ->post(route('staff.earning-bands.update', $bandId), [
                'band_name' => 'F2',
                'sort_order' => 31,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('earning_bands', [
            'id' => $bandId,
            'name' => 'F2',
            'sort_order' => 31,
        ]);

        $this->withoutMiddleware()
            ->actingAs($admin)
            ->delete(route('staff.earning-bands.destroy', $bandId))
            ->assertRedirect();

        $this->assertDatabaseMissing('earning_bands', [
            'id' => $bandId,
        ]);
    }

    public function test_assigned_earning_band_cannot_be_deleted(): void
    {
        $admin = $this->createUser('staff-settings-protected@example.com');
        $staff = $this->createUser('band-protected@example.com', 'B4');
        $bandId = DB::table('earning_bands')->where('name', 'B4')->value('id');

        $this->assertNotNull($staff->id);
        $this->assertNotNull($bandId);

        $this->withoutMiddleware()
            ->actingAs($admin)
            ->from(route('staff.staff-settings'))
            ->delete(route('staff.earning-bands.destroy', $bandId))
            ->assertRedirect(route('staff.staff-settings'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('earning_bands', [
            'id' => $bandId,
            'name' => 'B4',
        ]);
    }

    private function createUser(string $email, ?string $earningBand = null): User
    {
        return User::withoutEvents(fn () => User::query()->create([
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => $email,
            'password' => 'secret',
            'status' => 'Current',
            'position' => 'School Head',
            'department' => 'Academics',
            'area_of_work' => 'Teaching',
            'nationality' => 'Motswana',
            'phone' => '70123456',
            'id_number' => uniqid('id-', true),
            'earning_band' => $earningBand,
            'year' => 2026,
        ]));
    }
}

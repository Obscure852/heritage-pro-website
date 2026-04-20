<?php

namespace Tests\Unit\Pdp;

use App\Models\User;
use App\Models\UserProfileMetadata;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\EnsuresPdpPhaseOneSchema;
use Tests\TestCase;

class PdpUserProfileBackfillTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPdpPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensurePdpPhaseOneSchema();
    }

    public function test_backfill_copies_legacy_metadata_without_overwriting_existing_user_values(): void
    {
        $emptyUser = $this->createUser('backfill-empty@example.com');
        UserProfileMetadata::setValue($emptyUser->id, 'payroll_no', 'PAY-1001');
        UserProfileMetadata::setValue($emptyUser->id, 'grade', 'B4');
        UserProfileMetadata::setValue($emptyUser->id, 'dpsm_file_no', '81716');

        $existingUser = $this->createUser('backfill-existing@example.com', [
            'personal_payroll_number' => 'KEEP-2002',
            'dpsm_personal_file_number' => 'KEEP-DPSM-9',
            'earning_band' => 'C2',
        ]);
        UserProfileMetadata::setValue($existingUser->id, 'payroll_no', 'PAY-9999');
        UserProfileMetadata::setValue($existingUser->id, 'dpsm_file_no', 'DPSM-9999');
        UserProfileMetadata::setValue($existingUser->id, 'grade', 'Z9');

        $migration = require database_path('migrations/2026_03_12_000014_backfill_pdp_profile_fields_from_metadata.php');
        $migration->up();
        $migration = require database_path('migrations/2026_03_13_000002_add_dpsm_personal_file_number_to_users_and_patch_pdp_part_a.php');
        $migration->up();

        $this->assertSame('PAY-1001', $emptyUser->fresh()->personal_payroll_number);
        $this->assertSame('81716', $emptyUser->fresh()->dpsm_personal_file_number);
        $this->assertSame('B4', $emptyUser->fresh()->earning_band);
        $this->assertSame('KEEP-2002', $existingUser->fresh()->personal_payroll_number);
        $this->assertSame('KEEP-DPSM-9', $existingUser->fresh()->dpsm_personal_file_number);
        $this->assertSame('C2', $existingUser->fresh()->earning_band);
    }

    private function createUser(string $email, array $overrides = []): User
    {
        return User::withoutEvents(fn () => User::query()->create(array_merge([
            'firstname' => 'Backfill',
            'lastname' => 'User',
            'email' => $email,
            'password' => 'secret',
            'status' => 'Current',
            'position' => 'Teacher',
            'year' => 2026,
        ], $overrides)));
    }
}

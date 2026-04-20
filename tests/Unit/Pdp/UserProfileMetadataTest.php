<?php

namespace Tests\Unit\Pdp;

use App\Models\User;
use App\Models\UserProfileMetadata;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\EnsuresPdpPhaseOneSchema;
use Tests\TestCase;

class UserProfileMetadataTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPdpPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensurePdpPhaseOneSchema();
    }

    public function test_user_profile_metadata_can_store_scalars_and_arrays(): void
    {
        $user = User::factory()->create(['status' => 'Current']);

        UserProfileMetadata::setValue($user->id, 'payroll_no', '458618005');
        UserProfileMetadata::setValue($user->id, 'branding_preferences', ['show_logo' => true]);

        $this->assertSame('458618005', UserProfileMetadata::getValue($user->id, 'payroll_no'));
        $this->assertSame(['show_logo' => true], UserProfileMetadata::getValue($user->id, 'branding_preferences'));
        $this->assertSame('458618005', $user->getProfileMetadataValue('payroll_no'));
    }
}

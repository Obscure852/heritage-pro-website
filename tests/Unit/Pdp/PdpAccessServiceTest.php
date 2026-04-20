<?php

namespace Tests\Unit\Pdp;

use App\Models\Role;
use App\Models\User;
use App\Services\Pdp\PdpAccessService;
use App\Services\Pdp\PdpSettingsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\EnsuresPdpPhaseOneSchema;
use Tests\TestCase;

class PdpAccessServiceTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresPdpPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ensurePdpPhaseOneSchema();
        $this->ensureRolesTables();
    }

    public function test_elevated_access_respects_configured_positions_and_roles(): void
    {
        app(PdpSettingsService::class)->saveAccessSettings([
            'elevated_positions' => ['Deputy School Head'],
            'elevated_roles' => ['PDP Admin'],
        ]);

        $positionUser = $this->createUser('position-admin@example.com', ['position' => 'Deputy School Head']);
        $roleUser = $this->createUser('role-admin@example.com', ['position' => 'Teacher']);
        $role = Role::create(['name' => 'PDP Admin']);
        DB::table('role_users')->insert([
            'user_id' => $roleUser->id,
            'role_id' => $role->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(PdpAccessService::class);

        $this->assertTrue($service->hasElevatedAccess($positionUser));
        $this->assertTrue($service->hasElevatedAccess($roleUser->fresh()));
    }

    private function ensureRolesTables(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function ($table): void {
                $table->id();
                $table->string('name');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_users')) {
            Schema::create('role_users', function ($table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->timestamps();
            });
        }
    }

    private function createUser(string $email, array $overrides = []): User
    {
        return User::withoutEvents(fn () => User::query()->create(array_merge([
            'firstname' => 'Access',
            'lastname' => 'User',
            'email' => $email,
            'password' => 'secret',
            'status' => 'Current',
            'position' => 'Teacher',
            'year' => 2026,
        ], $overrides)));
    }
}

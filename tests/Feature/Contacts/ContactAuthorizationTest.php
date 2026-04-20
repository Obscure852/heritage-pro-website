<?php

namespace Tests\Feature\Contacts;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContactAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureRolesTables();
        $this->ensureSchoolSetupTable();
        $this->ensureContactsSchema();

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

    public function test_asset_management_view_user_can_access_contacts_index(): void
    {
        $user = $this->createUserWithRoles('asset-view@example.com', ['Asset Management View']);

        $this->actingAs($user)
            ->get(route('contacts.index'))
            ->assertOk();
    }

    public function test_asset_management_view_user_cannot_access_contact_management_pages(): void
    {
        $user = $this->createUserWithRoles('asset-view-restricted@example.com', ['Asset Management View']);

        $this->actingAs($user)
            ->get(route('contacts.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('contacts.settings'))
            ->assertForbidden();
    }

    public function test_asset_management_edit_user_can_access_contact_management_pages(): void
    {
        $user = $this->createUserWithRoles('asset-edit@example.com', ['Asset Management Edit']);

        $this->actingAs($user)
            ->get(route('contacts.create'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('contacts.settings'))
            ->assertOk();
    }

    public function test_administrator_can_access_contact_management_pages(): void
    {
        $user = $this->createUserWithRoles('administrator@example.com', ['Administrator']);

        $this->actingAs($user)
            ->get(route('contacts.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('contacts.create'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('contacts.settings'))
            ->assertOk();
    }

    public function test_unrelated_user_cannot_access_contacts_module(): void
    {
        $user = $this->createUserWithRoles('teacher-only@example.com', ['Teacher']);

        $this->actingAs($user)
            ->get(route('contacts.index'))
            ->assertForbidden();
    }

    private function ensureRolesTables(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_users')) {
            Schema::create('role_users', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->timestamps();
            });
        }
    }

    private function ensureSchoolSetupTable(): void
    {
        if (Schema::hasTable('school_setup')) {
            return;
        }

        Schema::create('school_setup', function (Blueprint $table): void {
            $table->id();
            $table->string('school_name')->nullable();
            $table->string('school_id')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureContactsSchema(): void
    {
        if (!Schema::hasTable('contacts')) {
            Schema::create('contacts', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique('contacts_name_unique');
                $table->string('email')->nullable();
                $table->string('phone', 50)->nullable();
                $table->text('address')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('contact_tags')) {
            Schema::create('contact_tags', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique('contact_tags_name_unique');
                $table->string('slug')->unique('contact_tags_slug_unique');
                $table->text('description')->nullable();
                $table->string('color', 20)->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('usable_in_assets')->default(false);
                $table->boolean('usable_in_maintenance')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('contact_people')) {
            Schema::create('contact_people', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('contact_id');
                $table->string('name');
                $table->string('title')->nullable();
                $table->string('email')->nullable();
                $table->string('phone', 50)->nullable();
                $table->boolean('is_primary')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('contact_contact_tag')) {
            Schema::create('contact_contact_tag', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('contact_id');
                $table->unsignedBigInteger('contact_tag_id');
                $table->timestamps();
                $table->unique(['contact_id', 'contact_tag_id']);
            });
        }

        if (!Schema::hasTable('assets')) {
            Schema::create('assets', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('asset_code')->unique();
                $table->unsignedBigInteger('contact_id')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('status')->default('Available');
                $table->string('condition')->default('Good');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('asset_maintenances')) {
            Schema::create('asset_maintenances', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('asset_id')->nullable();
                $table->unsignedBigInteger('contact_id')->nullable();
                $table->string('maintenance_type')->nullable();
                $table->date('maintenance_date')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }
    }

    private function createUserWithRoles(string $email, array $roles, array $overrides = []): User
    {
        $user = User::withoutEvents(fn () => User::query()->create(array_merge([
            'firstname' => 'Contact',
            'lastname' => 'Tester',
            'email' => $email,
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
}

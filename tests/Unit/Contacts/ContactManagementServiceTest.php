<?php

namespace Tests\Unit\Contacts;

use App\Models\Contact;
use App\Models\ContactTag;
use App\Services\Contacts\ContactManagementService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContactManagementServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureContactsSchema();
    }

    public function test_save_enforces_exactly_one_primary_person_and_syncs_tags(): void
    {
        $vendorTag = ContactTag::query()->firstOrCreate([
            'name' => 'Vendor',
        ], [
            'slug' => 'vendor',
            'is_active' => true,
            'usable_in_assets' => true,
            'usable_in_maintenance' => true,
        ]);

        $service = app(ContactManagementService::class);
        $businessName = 'Northwind Supplies ' . uniqid();
        $contact = $service->save(null, [
            'name' => $businessName,
            'is_active' => true,
        ], [
            [
                'name' => 'Alice',
                'title' => 'Manager',
                'is_primary' => false,
            ],
            [
                'name' => 'Bob',
                'title' => 'Director',
                'is_primary' => true,
            ],
        ], [$vendorTag->id]);

        $this->assertSame($businessName, $contact->name);
        $this->assertCount(2, $contact->people);
        $this->assertSame(1, $contact->people->where('is_primary', true)->count());
        $this->assertSame('Bob', $contact->primaryPerson?->name);
        $this->assertSame([$vendorTag->id], $contact->tags->pluck('id')->all());
    }

    public function test_resolve_import_contact_by_name_reuses_existing_contact(): void
    {
        ContactTag::query()->firstOrCreate([
            'name' => 'Vendor',
        ], [
            'slug' => 'vendor',
            'is_active' => true,
            'usable_in_assets' => true,
            'usable_in_maintenance' => true,
        ]);

        $service = app(ContactManagementService::class);
        $businessName = 'Acme Repairs ' . uniqid();

        $first = $service->resolveImportContactByName($businessName);
        $second = $service->resolveImportContactByName($businessName);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Contact::query()->where('name', $businessName)->count());
        $this->assertSame('vendor', $first->tags->first()?->slug);
    }

    public function test_delete_blocks_when_contact_is_linked_to_assets(): void
    {
        $contact = Contact::query()->create([
            'name' => 'Linked Contact ' . uniqid(),
            'is_active' => true,
        ]);

        if (!Schema::hasTable('asset_categories')) {
            Schema::create('asset_categories', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        \DB::table('asset_categories')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'IT Equipment',
                'code' => 'IT',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        \DB::table('assets')->insert([
            'id' => 1,
            'name' => 'Asset',
            'asset_code' => 'ASSET-001',
            'category_id' => 1,
            'contact_id' => $contact->id,
            'status' => 'Available',
            'condition' => 'Good',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(ContactManagementService::class)->delete($contact);
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
                $table->unsignedBigInteger('category_id')->nullable();
                $table->unsignedBigInteger('contact_id')->nullable();
                $table->string('status')->default('Available');
                $table->string('condition')->default('Good');
                $table->timestamps();
                $table->softDeletes();
            });
        } elseif (!Schema::hasColumn('assets', 'contact_id')) {
            Schema::table('assets', function (Blueprint $table): void {
                $table->unsignedBigInteger('contact_id')->nullable();
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
        } elseif (!Schema::hasColumn('asset_maintenances', 'contact_id')) {
            Schema::table('asset_maintenances', function (Blueprint $table): void {
                $table->unsignedBigInteger('contact_id')->nullable();
            });
        }
    }
}

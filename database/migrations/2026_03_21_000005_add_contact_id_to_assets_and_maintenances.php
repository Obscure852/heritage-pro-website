<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('contact_id')->nullable()->after('category_id')->constrained('contacts')->nullOnDelete();
        });

        Schema::table('asset_maintenances', function (Blueprint $table) {
            $table->foreignId('contact_id')->nullable()->after('next_maintenance_date')->constrained('contacts')->nullOnDelete();
        });

        if (Schema::hasTable('asset_vendors')) {
            $vendorRows = DB::table('asset_vendors')->orderBy('id')->get();
            $vendorTagId = DB::table('contact_tags')->where('slug', 'vendor')->value('id');
            $vendorIdToContactId = [];

            foreach ($vendorRows as $vendor) {
                $contactId = DB::table('contacts')
                    ->where('name', $vendor->name)
                    ->value('id');

                if (!$contactId) {
                    DB::table('contacts')->updateOrInsert(
                        ['id' => $vendor->id],
                        [
                            'name' => $vendor->name,
                            'email' => $vendor->email,
                            'phone' => $vendor->phone,
                            'address' => $vendor->address,
                            'notes' => $vendor->notes,
                            'is_active' => (bool) $vendor->is_active,
                            'created_at' => $vendor->created_at ?? now(),
                            'updated_at' => $vendor->updated_at ?? now(),
                            'deleted_at' => $vendor->deleted_at ?? null,
                        ]
                    );

                    $contactId = DB::table('contacts')
                        ->where('name', $vendor->name)
                        ->value('id');
                }

                $vendorIdToContactId[$vendor->id] = $contactId;

                if ($vendorTagId) {
                    DB::table('contact_contact_tag')->updateOrInsert(
                        [
                            'contact_id' => $contactId,
                            'contact_tag_id' => $vendorTagId,
                        ],
                        [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }

                if (!empty($vendor->contact_person)) {
                    $personAlreadyExists = DB::table('contact_people')
                        ->where('contact_id', $contactId)
                        ->where('name', $vendor->contact_person)
                        ->exists();

                    if (!$personAlreadyExists) {
                        $existingPrimary = DB::table('contact_people')
                            ->where('contact_id', $contactId)
                            ->where('is_primary', true)
                            ->exists();

                        $sortOrder = (int) DB::table('contact_people')
                            ->where('contact_id', $contactId)
                            ->max('sort_order');

                        DB::table('contact_people')->insert([
                            'contact_id' => $contactId,
                            'name' => $vendor->contact_person,
                            'title' => null,
                            'email' => null,
                            'phone' => null,
                            'is_primary' => !$existingPrimary,
                            'sort_order' => $existingPrimary ? $sortOrder + 1 : 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            foreach ($vendorIdToContactId as $vendorId => $contactId) {
                DB::table('assets')
                    ->whereNull('contact_id')
                    ->where('vendor_id', $vendorId)
                    ->update(['contact_id' => $contactId]);

                DB::table('asset_maintenances')
                    ->whereNull('contact_id')
                    ->where('vendor_id', $vendorId)
                    ->update(['contact_id' => $contactId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('asset_maintenances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contact_id');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contact_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('customers') || ! Schema::hasTable('leads')) {
            return;
        }

        DB::table('customers')
            ->whereNull('lead_id')
            ->orderBy('id')
            ->chunkById(100, function ($customers) {
                foreach ($customers as $customer) {
                    $createdAt = $customer->created_at ?? now();
                    $updatedAt = $customer->updated_at ?? now();
                    $convertedAt = $customer->purchased_at ?: $createdAt ?: now();

                    $leadId = DB::table('leads')->insertGetId([
                        'owner_id' => $customer->owner_id,
                        'company_name' => $customer->company_name,
                        'industry' => $customer->industry,
                        'website' => $customer->website,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                        'country' => $customer->country,
                        'status' => 'converted',
                        'converted_at' => $convertedAt,
                        'notes' => $customer->notes,
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                        'deleted_at' => null,
                    ]);

                    DB::table('customers')
                        ->where('id', $customer->id)
                        ->update([
                            'lead_id' => $leadId,
                            'updated_at' => $updatedAt,
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Intentionally left blank. Backfilled source leads should be preserved.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('contacts')) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) {
            if (! Schema::hasColumn('contacts', 'owner_id')) {
                $table->foreignId('owner_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('contacts', 'lead_id')) {
                $table->foreignId('lead_id')->nullable()->after('owner_id')->constrained('leads')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('contacts', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('lead_id')->constrained('customers')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('contacts', 'job_title')) {
                $table->string('job_title')->nullable()->after('name');
            }

            if (! Schema::hasColumn('contacts', 'is_primary')) {
                $table->boolean('is_primary')->default(false)->after('phone');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('contacts')) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) {
            $dropColumns = [];

            foreach (['job_title', 'is_primary', 'customer_id', 'lead_id', 'owner_id'] as $column) {
                if (Schema::hasColumn('contacts', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if (Schema::hasColumn('contacts', 'owner_id')) {
                $table->dropConstrainedForeignId('owner_id');
            }

            if (Schema::hasColumn('contacts', 'lead_id')) {
                $table->dropConstrainedForeignId('lead_id');
            }

            if (Schema::hasColumn('contacts', 'customer_id')) {
                $table->dropConstrainedForeignId('customer_id');
            }

            $dropColumns = array_values(array_diff($dropColumns, ['owner_id', 'lead_id', 'customer_id']));

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
